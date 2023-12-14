<?php
namespace App\Controller;

use App\Core\Collections\Collection;
use App\Core\Collections\CollectionList;
use App\Database\OpnimusDatabase;
use App\RestClient\RestClient;
use App\RestClient\RestClientException;

class PortController extends ApiController
{
    public function index($request, $response)
    {
        if(!$this->isAuthorized()) {
            return $this->toErrorJsonResponse($response, 'Not Authorized');
        }

        $api = new RestClient('https://newosase.telkom.co.id/api/v1');
        $params = $this->getAvailableParams($request);
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
            $response = $api->sendRequest('GET', '/dashboard-service/dashboard/rtu/port-sensors');
            if($response && is_array($response->result->payload)) {
                $ports = $response->result->payload;
            }

        } catch(\Throwable $err) {
            return $this->toJsonResponse($response, [ 'Status' => 'No Data' ]);
        }

        $data = new CollectionList();
        foreach($ports as $item) {

            $port = new Collection();
            $port->RTU_ID = $item->rtu_sname ?? null;
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
            // $port->DATEL = $item ?? null;
            // $port->DATEL_KODE = $item ?? null;
            $port->WITEL = $item->witel ?? null;
            // $port->WITEL_KODE = $item ?? null;
            $port->DIVRE = $item->regional ?? null;
            // $port->DIVRE_KODE = $item ?? null;

            $data->push($port);

        }

        return $this->toJsonResponse($response, $data->toArray());
    }

    protected function getAvailableParams($request)
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

        $params = $request->getQueryParams();
        $data = new Collection();
        foreach($keys as $key) {
            $data->$key = isset($params[$key]) ? $params[$key] : null; 
        }
        return $data;
    }
}