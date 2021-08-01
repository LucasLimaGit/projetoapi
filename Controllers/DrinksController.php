<?php 
namespace Controllers;

use \Core\Controller;
use \Models\Drinks;
use \Models\Users;

class DrinksController extends Controller {
    
    public function new_record($id){
        $array = array('error'=>'', 'logged'=>false);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        $users = new Users();
        
        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;
            
            $array['is_me'] = false;
            if($id == $users->getId()) {
                $array['is_me'] = true;
            
                $drinks = new Drinks();
            
                switch ($method) {
                    case 'POST':
                        if(!empty($data['drink_ml'])) {                            
                            $drinks->create($id, $data['drink_ml']);                           
                        } else {
                            $array['error'] = 'Dados não preenchidos.';
                        }
                        break;
                    case 'GET': 
                        $data = $drinks->getInfoAll($id);
                        if(count($data) > 0) {
                            $array['data'] = $data;
                        } else {
                            $array['error'] = 'O usuário não possui registros no sistema.';
                        }
                        break;
                    default:
                        $array['error'] = 'Método de requisição '.$method.' não disponível.';
                        break;
                }
            } else {
                $array['error'] = 'Não é permitido acessar/inserir informações em outro usuário.';
            }
            
        } else {
            $array['error'] = 'Acesso negado.';
        }
        
        $this->returnJson($array);
    }
    
    public function ranking() {
        $array = array('error'=>'');
        
        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        if($method == 'GET') {
            $drinks = new Drinks();
            $array['data'] = $drinks->getRanking();
                    
            for($i = 0; $i < count($array['data']); $i++) {
                $array['data'][$i]['rank'] = $i + 1;
            }
        } else {
            $array['error'] = 'Método de requisição incompatível.';
        }
        
        $this->returnJson($array);        
    }
    
}