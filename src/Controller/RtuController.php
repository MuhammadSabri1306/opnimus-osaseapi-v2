<?php
namespace App\Controller;

use App\Core\Collections\Collection;
use App\Core\Collections\CollectionList;
use App\Database\Database;
use App\Database\OpnimusDatabase;
use App\RestClient\RestClient;
use App\RestClient\RestClientException;

use App\Controller\ApiController;

class RtuController extends ApiController
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
            $apiParams->datel = "%$params->datel%";
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
                    $item->IP_RTU = null;
                    $item->ID_MD = null;
                    $item->MD_NAME = null;
                    $item->MD_IP = null;
                    $item->LOKASI = $rtu->locs_name ?? null;
                    $item->TIPE_SITE = null;
                    $item->DATEL_KODE = null;
                    $item->DATEL = $rtu->name ?? null;
                    $item->WITEL_KODE = $rtuData ? $rtuData->witel_code : null;
                    $item->WITEL = $witel->name ?? null;
                    $item->DIVRE_KODE = $rtuData ? $rtuData->regional_code : null;
                    $item->DIVRE = $regional->name ?? null;
                    
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
            'rtuid'
        ];

        $params = $this->request->getQueryParams();
        $data = new Collection();
        foreach($keys as $key) {
            $data->set($key, isset($params[$key]) ? $params[$key] : null); 
        }
        return $data;
    }

    protected function getRtus($dbOpnimusNew, $regionalId = null, $witelId = null, $rtuSname = null)
    {
        $params = [];
        if($regionalId) {
            array_push($params, [
                'query' => 'rtu.regional_id=%i_treg',
                'bind' => [ 'treg' => $regionalId ]
            ]);
        }
        if($witelId) {
            array_push($params, [
                'query' => 'rtu.witel_id=%i_witel',
                'bind' => [ 'witel' => $witelId ]
            ]);
        }
        if($rtuSname) {
            array_push($params, [
                'query' => 'rtu.sname=%s_rtu',
                'bind' => [ 'rtu' => $rtuSname ]
            ]);
        }

        $query = 'SELECT rtu.sname AS rtu_sname, datel.datel_name, witel.witel_name, witel.witel_code,'.
            ' treg.name AS regional_name, treg.divre_code AS regional_code FROM rtu_list AS rtu'.
            ' LEFT JOIN regional AS treg ON treg.id=rtu.regional_id'.
            ' LEFT JOIN witel ON witel.id=rtu.witel_id'.
            ' LEFT JOIN datel ON datel.id=rtu.datel_id';

        try {
            if(count($params) > 0) {
    
                $queryWhere = implode(' AND ', array_column($params, 'query'));
                $query = "$query WHERE $queryWhere";
                $values = array_merge( ...array_column($params, 'bind') );
                $rtus = $dbOpnimusNew->query($query, $values);
                
            } else {
                
                $values = null;
                $rtus = $dbOpnimusNew->query($query);
    
            }
        } catch(\Throwable $err) {
            $errDetails = [ 'query' => $query, 'bind' => $values ];
            dd( (string) $err, $errDetails );
        }

        return new CollectionList($rtus ?? []);
    }
}