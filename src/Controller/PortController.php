<?php
namespace App\Controller;

use App\Core\Collections\Collection;
use App\Core\Collections\CollectionList;
use App\Database\OpnimusDatabase;
use App\RestClient\RestClient;
use App\RestClient\RestClientException;

class PortController extends ApiController
{
    public function index()
    {
        if(!$this->isAuthorized()) {
            return $this->toErrorJsonResponse('Not Authorized');
        }

        $api = new RestClient('https://newosase.telkom.co.id/api/v1');
        $params = $this->getAvailableParams();
        $apiParams = new Collection();

        $dbOpnimusNew = new OpnimusDatabase('juan5684_opnimus_new');

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
            $datelId = $dbOpnimusNew->queryFirstField('SELECT id FROM datel WHERE datel_name=%s', $params->datel);
            if($datelId) {
                $apiParams->datelId = $datelId;
            }
        }

        if(isset($params->lokasi)) {
            $locId = $dbOpnimusNew->queryFirstField('SELECT id FROM rtu_location WHERE location_sname=%s', $params->lokasi);
            if($locId) {
                $apiParams->locationId = $locId;
            }
        }

        if(isset($params->rtuid)) {
            $apiParams->searchRtuSname = $params->rtuid;
        }

        if(isset($params->namartu)) {
            $apiParams->searchRtuName = "%$params->namartu%";
        }

        if(isset($params->port)) {
            $apiParams->searchNoPort = $params->port;
        }

        if(isset($params->tipeport)) {
            $apiParams->searchIdentifier = $params->tipeport;
        }

        if(isset($params->satuan)) {
            $apiParams->searchUnits = $params->satuan;
        }

        if(isset($params->flag)) {
            $apiParams->isAlert = $params->flag;
        }

        $ports = [];
        try {

            $api->request['query'] = $apiParams->toArray();
            $api->request['verify'] = false;
            $api->request['headers'] = [ 'Accept' => 'application/json' ];

            $response = $api->sendRequest('GET', '/dashboard-service/dashboard/rtu/port-sensors');
            if($response && is_array($response->result->payload)) {
                $ports = $response->result->payload;
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
        $rtuSname = $apiParams->get('searchRtuSname', null);
        $rtus = $this->getRtus($dbOpnimusNew, $regionalId, $witelId, $rtuSname);

        $data = new CollectionList();
        foreach($ports as $item) {

            $itemRtuSname = $item->rtu_sname ?? null;
            $rtuData = $rtus->find(fn($rtu) => $rtu->rtu_sname == $itemRtuSname);

            $port = new Collection();
            $port->RTU_ID = $itemRtuSname;
            $port->NAMA_RTU = $item->rtu_name ?? null;
            $port->PORT = $item->no_port ?? null;
            $port->NAMA_PORT = $item->port_name ?? null;
            $port->TIPE_PORT = $item->identifier ?? null;
            // $port->JENIS_PORT = $item ?? null;
            $port->SATUAN = $item->units ?? null;
            $port->VALUE = $item->value ?? null;
            $port->STATUS = isset($item->severity->name) ? $item->severity->name : null;
            // $port->DURASI = $item ?? null;
            // $port->TIPE_SITE = $item ?? null;
            $port->LOKASI = $item->location ?? null;
            $port->DATEL = $rtuData ? $rtuData->datel_name : null;
            // $port->DATEL_KODE = $item ?? null;
            $port->WITEL = $item->witel ?? null;
            $port->WITEL_KODE = $rtuData ? $rtuData->witel_code : null;
            
            $port->DIVRE = $item->regional ?? null;
            $port->DIVRE_KODE = $rtuData ? $rtuData->regional_code : null;

            $data->push($port);

        }

        return $this->toJsonResponse($data->toArray());
    }

    protected function getAvailableParams()
    {
        $keys = [
            'divre',
            'witel',
            'datel',
            // 'tipesite',
            'lokasi',
            'rtuid',
            'namartu',
            'port',
            'tipeport',
            // 'namaport',
            // 'value',
            'satuan',
            // 'durasi',
            'flag',
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