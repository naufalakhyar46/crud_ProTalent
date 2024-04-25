<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $produkRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function index(Request $request){
        return $this->userRepository->getListData($request);
    }

    public function show($id){
        $dt = $this->userRepository->getUser($id);
        return success(['data'=>$dt]);
    }

    public function store(Request $request){
        $save = $this->userRepository->saveData($request);
        if($save[0] == 'sukses'){
            return successForm('add');
        }elseif($save[0] == 'errorForm'){
            return errorForm($save[1]);
        }else{
            return errorRespApi();
        }
    }

    public function update(Request $request,$id){
        $save = $this->userRepository->saveData($request,$id);
        if($save[0] == 'sukses'){
            return successForm('update');
        }elseif($save[0] == 'errorForm'){
            return errorForm($save[1]);
        }else{
            return errorRespApi();
        }
    }

    public function destroy($id){
        $save = $this->userRepository->destroyData($id);
        if($save[0] == 'sukses'){
            return successForm('delete');
        }else{
            return errorRespApi();
        }
    }
}
