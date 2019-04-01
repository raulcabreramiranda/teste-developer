<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Validator;
use Cache;
use App\Repositories\OrdersRepository;
use App\Repositories\EloquentOrdersRepository;
use App\Repositories\ElasticsearchOrdersRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class OrdersController extends Controller
{

    public function __construct() {

    }

    public function index(Request $request)
    {
        // Obtendo os parametros da URL
        $orderByField = $request->get('order_by_field','id');
        $orderByOrder = $request->get('order_by_order','ASC');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $filterUserId = $request->get('user_id','');
        $filterItemDescription = $request->get('item_description','');
        $filterItemQuantity = $request->get('item_quantity','');
        $filterItemPrice = $request->get('item_price','');

        // Salvando na cache esta consulta
        $cacheKey = "list-orders-".md5($orderByField . $orderByOrder . $page . $limit . $filterUserId . $filterItemDescription . $filterItemQuantity . $filterItemPrice);
        $replay = Cache::remember($cacheKey, 22*60, function()  use ($orderByField, $orderByOrder, $page, $limit, $filterUserId, $filterItemDescription, $filterItemQuantity, $filterItemPrice){
            // Creando a consulta com a ordem, os filtros e a paginação
            $ordersQuery = Order::orderBy($orderByField, $orderByOrder);

            if($filterUserId !== '') $ordersQuery->where('user_id', '=', $filterUserId);
            if($filterItemDescription !== '') $ordersQuery->where('item_description', 'like', "%{$filterItemDescription}%");
            if($filterItemQuantity !== '') $ordersQuery->where('item_quantity', 'like', "%{$filterItemQuantity}%");
            if($filterItemPrice !== '') $ordersQuery->where('item_price', 'like', "%{$filterItemPrice}%");

            $ordersQuery->skip(($page-1)*$limit);
            $ordersQuery->take($limit);

            $rowCount = $ordersQuery->count();
            $orders = $ordersQuery->get();
            return array(
                'itens' => $orders,
                'page' => $page,
                'limit' => $limit,
                'count' => $rowCount
            );
        });

        return response()->json($replay);
    }

    public function search(OrdersRepository $repository, Request $request)
    {
        $orders = $repository->search($request->get('q', ''));

        return response()->json($orders);
    }

    public function searchByUser(OrdersRepository $repository, Request $request)
    {

        $userName = $request->get('q', '');
        $client = new Client(['http_errors' => false]);
        // Obtendo os dados do usuario
        $resUser = $client->request('GET', config('services.user_api.path').'/search_ids?q='.$userName);

        if($resUser->getStatusCode() !== 200) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => 'Usuario não encontrado'
            ], 422);
        }

        $userIds = json_decode($resUser->getBody());

        $orders = $repository->searchByUserId($userIds);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        // Obtendo os dados do usuario
        $data = json_decode($request->getContent(), true);

        // Validando os dados do usuario antes de criar
        $validator = Validator::make($data, [
            'user_id' => 'required|max:100',
            'item_description' => 'required',
            'item_quantity' => 'required',
            'item_price' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        $client = new Client(['http_errors' => false]);
        // Obtendo os dados do usuario
        $resUser = $client->request('GET', config('services.user_api.path').'/'.$data['user_id']);
        if($resUser->getStatusCode() !== 200) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => 'Usuario não encontrado'
            ], 422);
        }



        // Criando o usuario
        $order = new Order();
        $order->fill($data);
        $order->total_value = $data['item_price'] * $data['item_quantity'];
        Cache::flush();
        $order->save();

        // Retornando dados do novo usuario em formato json
        return response()->json($order, 201);
    }

    public function show($id)
    {

        // Salvando na cache este usuario
        $cacheKey = "show-order-id-".$id;
        $order = Cache::remember($cacheKey, 22*60, function() use ($id) {
            // Obtendo os dados do usuario
            return Order::find($id);
        });

        // Verificar se o usuário existe
        if(!$order) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        // Retornando dados do usuario em formato json
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        // Obtendo os dados do usuario
        $order = Order::find($id);
        $data = json_decode($request->getContent(), true);

        // Verificar se o usuário existe
        if(!$order) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        // Apagando o email dos dados, se e igual
        if(array_key_exists('email', $data) && $order->email == $data['email']) {
            unset($data['email']);
        }

        // Validando os dados  do usuario antes de salvar
        $validator = Validator::make($data, [
            'user_id' => 'required|max:100',
            'item_description' => 'required',
            'item_quantity' => 'required',
            'item_price' => 'required',
        ]);
        if($validator->fails()) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        // Salvando dados do usuario
        $order->fill($data);
        $order->total_value = $data['item_price'] * $data['item_quantity'];
        Cache::flush();
        $order->save();

        // Retornando dados do usuario em formato json
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::find($id);

        if(!$order) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        Cache::flush();
        return response()->json($order->delete(), 204);
    }

}
