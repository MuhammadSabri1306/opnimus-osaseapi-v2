<?php
namespace App\Controller;

use App\Core\Collections\Collection;
use App\Core\Collections\CollectionList;
use App\Database\Database;
use App\Database\OpnimusDatabase;
use App\RestClient\RestClient;
use App\RestClient\RestClientException;
use App\Controller\RtuController;

class RtuStatusController extends RtuController
{
    public function index()
    {
        if(!$this->isAuthorized()) {
            return $this->toErrorJsonResponse('Not Authorized');
        }

        $params = $this->getAvailableParams();
        $dbOpnimusNew = new OpnimusDatabase('juan5684_opnimus_new');

        $api = new RestClient('https://newosase.telkom.co.id/api/v1');
        $apiParams = new Collection();

        if(isset($params->divre)) {
            $regionalId = $dbOpnimusNew->queryFirstField('SELECT id FROM regional WHERE divre_code=%s', $params->divre);
            if($regionalId) {
                $apiParams->regionalId = $regionalId;
            }
        }

        if(isset($params->witel)) {
            $witelId = $dbOpnimusNew->queryFirstField('SELECT id FROM witel WHERE witel_code=%s', $params->witel);
            if($witelId) {
                $apiParams->witelId = $witelId;
            }
        }

        if(isset($params->datel)) {
            $searchDatelName = strtoupper($params->datel);
            $apiParams->datel = "%$searchDatelName%";
        }

        if(isset($params->rtustatus)) {
            $searchRtuStatus = strtolower($params->rtustatus);
            $apiParams->statusRtu = "%$searchRtuStatus%";
        }

        $apiParams->isArea = 'hide';
        $apiParams->level = 1;
        $apiParams->isChildren = 'view';

        $rtuMap = [];
        try {

            $api->request['query'] = $apiParams->toArray();
            $api->request['verify'] = false;
            $api->request['headers'] = [ 'Accept' => 'application/json' ];

            $response = $api->sendRequest('GET', '/parameter-service/mapview');
            if($response && is_array($response->result)) {
                $rtuMap = $response->result;
            }

        } catch(RestClientException $err) {
            return $this->toJsonResponse([
                'Status' => 'No Data',
                'request' => $err->getRequest(),
                'response' => $err->getResponse(),
            ]);
        } catch(\Throwable $err) {
            return $this->toJsonResponse([
                'Status' => 'No Data',
                'Message' => (string) $err
            ]);
        }

        $regionalId = $apiParams->get('regionalId', null);
        $witelId = $apiParams->get('witelId', null);
        $rtuSname = $params->get('rtuid', null);
        $rtus = $this->getRtus($dbOpnimusNew, $regionalId, $witelId, $rtuSname);

        $rtuList = new CollectionList();
        foreach($rtuMap as $regional) {
            foreach($regional->witel as $witel) {
                foreach($witel->rtu as $rtu) {

                    $itemRtuSname = $rtu->rtu_sname ?? null;
                    $rtuData = $rtus->find(fn($rtuItem) => $rtuItem->rtu_sname == $itemRtuSname);
                    
                    $item = new Collection();
                    
                    $item->RTU_ID = $itemRtuSname;
                    $item->NAMA_RTU = $rtu->rtu_name ?? null;
                    $item->RTU_STATUS = strtoupper($rtu->rtu_status) ?? null;
                    $item->MD_STATUS = null;
                    $item->TIPE_SITE = null;
                    $item->KOTA = $rtu->locs_name ?? null;
                    $item->LOKASI = $rtu->locs_name ?? null;
                    $item->DATEL = $rtu->name ?? null;
                    $item->DATEL_KODE = null;
                    $item->WITEL = $witel->name ?? null;
                    $item->WITEL_KODE = $rtuData ? $rtuData->witel_code : null;
                    $item->DIVRE = $regional->name ?? null;
                    $item->DIVRE_KODE = $rtuData ? $rtuData->regional_code : null;
                    
                    if($rtuSname && $rtuData) {
                        $rtuList->push($item);
                    } elseif(!$rtuSname) {
                        $rtuList->push($item);
                    }

                }
            }
        }

        return $this->toJsonResponse($rtuList->toArray());
    }

    protected function getAvailableParams()
    {
        $keys = [
            'divre',
            'witel',
            'datel',
            // 'tipesite',
            'rtuid',
            'rtustatus'
        ];

        $params = $this->request->getQueryParams();
        $data = new Collection();
        foreach($keys as $key) {
            $data->set($key, isset($params[$key]) ? $params[$key] : null); 
        }
        return $data;
    }
}