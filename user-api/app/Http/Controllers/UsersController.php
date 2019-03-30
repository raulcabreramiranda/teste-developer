<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Validator;
use Cache;
use App\Repositories\UsersRepository;
use App\Repositories\EloquentUsersRepository;
use App\Repositories\ElasticsearchUsersRepository;

class UsersController extends Controller
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
        $filterName = $request->get('name', '');
        $filterCpf = $request->get('cpf', '');
        $filterEmail = $request->get('email', '');
        $filterPhoneNumber = $request->get('phone_number', '');

        // Salvando na cache esta consulta
        $cacheKey = "list-users-".md5($orderByField . $orderByOrder . $page . $limit . $filterName . $filterCpf . $filterEmail . $filterPhoneNumber);
        $replay = Cache::remember($cacheKey, 22*60, function()  use ($orderByField, $orderByOrder, $page, $limit, $filterName, $filterCpf, $filterEmail, $filterPhoneNumber){
            // Creando a consulta com a ordem, os filtros e a paginação
            $usersQuery = User::orderBy($orderByField, $orderByOrder);

            if($filterName !== '') $usersQuery->where('name', 'like', "%{$filterName}%");
            if($filterCpf !== '') $usersQuery->where('cpf', 'like', "%{$filterCpf}%");
            if($filterEmail !== '') $usersQuery->where('email', 'like', "%{$filterEmail}%");
            if($filterPhoneNumber !== '') $usersQuery->where('phone_number', 'like', "%{$filterPhoneNumber}%");

            $usersQuery->skip(($page-1)*$limit);
            $usersQuery->take($limit);

            $rowCount = $usersQuery->count();
            $users = $usersQuery->get();
            return array(
                'itens' => $users,
                'page' => $page,
                'limit' => $limit,
                'count' => $rowCount
            );
        });

        return response()->json($replay);
    }

    public function search(UsersRepository $repository, Request $request)
    {
        $users = $repository->search($request->get('q', ''));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        // Obtendo os dados do usuario
        $data = json_decode($request->getContent(), true);

        // Validando os dados do usuario antes de criar
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'cpf' => 'required|max:14',
            'email' => 'required|email|unique:users',
        ]);
        if($validator->fails()) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => $validator->errors()->all()
            ], 422);
        }


        // Criando o usuario
        $user = new User();
        $user->fill($data);
        Cache::flush();
        $user->save();

        // Retornando dados do novo usuario em formato json
        return response()->json($user, 201);
    }

    public function show($id)
    {

        // Salvando na cache este usuario
        $cacheKey = "show-user-id-".$id;
        $user = Cache::remember($cacheKey, 22*60, function() use ($id) {
            // Obtendo os dados do usuario
            return User::find($id);
        });

        // Verificar se o usuário existe
        if(!$user) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        // Retornando dados do usuario em formato json
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        // Obtendo os dados do usuario
        $user = User::find($id);
        $data = json_decode($request->getContent(), true);

        // Verificar se o usuário existe
        if(!$user) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        // Apagando o email dos dados, se e igual
        if(array_key_exists('email', $data) && $user->email == $data['email']) {
            unset($data['email']);
        }

        // Validando os dados  do usuario antes de salvar
        $validator = Validator::make($data, [
            'name' => 'max:100',
            'cpf' => 'required|max:14',
            'email' => 'email|unique:users',
        ]);
        if($validator->fails()) {
            return response()->json([
                'message'   => 'Falha na validação',
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        // Salvando dados do usuario
        $user->fill($data);
        Cache::flush();
        $user->save();

        // Retornando dados do usuario em formato json
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message'   => 'Usuario não encontrado',
            ], 404);
        }

        Cache::flush();
        return response()->json($user->delete(), 204);
    }

}
