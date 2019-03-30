<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Validator;

class UsersController extends Controller
{

    public function __construct() {

    }

    public function index()
    {
        $users = User::all();
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
        $user->save();

        // Retornando dados do novo usuario em formato json
        return response()->json($user, 201);
    }

    public function show($id)
    {
        // Obtendo os dados do usuario
        $user = User::find($id);

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

        return response()->json($user->delete(), 204);
    }

}
