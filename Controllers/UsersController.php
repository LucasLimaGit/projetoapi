<?php 
namespace Controllers;

use \Core\Controller;
use \Models\Users;

class UsersController extends Controller {
    
    public function index() {}
    
    public function login() {        
        $array = array('error'=>'');
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        if($method == 'POST') {
            
            if(!empty($data['email']) && !empty($data['password'])) {
                $users = new Users();                
                if($users->checkCredentials($data['email'], $data['password'])) {
                    $arrayResult = $users->createJwt(true);
                    $array['jwt'] = $arrayResult['jwt'];
                    $array['data'] = $arrayResult['data'];
                } else {
                    $array['error'] = 'Usuário ou senha inválidos.';
                }                
            } else {
                $array['error'] = 'Dados não preenchidos.';
            }            
        } else {
            $array['error'] = 'Método de requisição incompatível.';
        }
        
        $this->returnJson($array);
    }
    
    public function new_record() {
        $array = array('error'=>'');
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        
        switch ($method) {
            case 'POST':
                if(!empty($data['name']) && !empty($data['email']) && !empty($data['password'])) {
                    if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        if($users->create($data['name'], $data['email'], $data['password'])) {
                            $array['jwt'] = $users->createJwt();
                        } else {
                            $array['error'] = 'E-mail já existente.';
                        }
                    } else {
                        $array['error'] = 'E-mail inválido.';
                    }
                    
                } else {
                    $array['error'] = 'Dados não preenchidos.';
                }
                break;
            case 'GET':
                $array['logged'] = false;  
                if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
                    $array['logged'] = true;                     
                    
                    $offset = 0;
                    $limit = 3;
                    $total = $users->getTotal();
                    $array['pages'] = ceil($total/$limit);
                    
                    $frontPage = 1;
                    if(!empty($data['page'])) {
                        $frontPage = intval($data['page']);
                    }
                    
                    $offset = ($frontPage * $limit) - $limit;
                    
                    $array['data'] = $users->getInfoAll($offset, $limit);                    
                } else {
                    $array['error'] = 'Acesso negado.';
                }
                break;
            default:
                $array['error'] = 'Método de requisição '.$method.' não disponível.';
                break;
        }       
        
        $this->returnJson($array);
    }
    
    public function view($id) {
        $array = array('error'=>'', 'logged'=>false);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;            
            
            $array['is_me'] = false;
            if($id == $users->getId()) {
                $array['is_me'] = true;
            }
            
            switch ($method) {
                case 'GET':
                    $array['data'] = $users->getInfo($id);
                    
                    if(count($array['data']) === 0) {
                        $array['error'] = 'Usuário não existe.';
                    }
                    break;
                case 'PUT':
                    $array['error'] = $users->editInfo($id, $data);
                    break;
                case 'DELETE':
                    $array['error'] = $users->delete($id);
                    break;
                default:
                    $array['error'] = 'Método de requisição '.$method.' não disponível.';
                    break;
            }
            
        } else {
            $array['error'] = 'Acesso negado.';
        }
        
        $this->returnJson($array);        
    }
    
}