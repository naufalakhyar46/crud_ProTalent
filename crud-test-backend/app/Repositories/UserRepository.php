<?php
namespace App\Repositories;
use File;
use Image;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use DataTables;

class UserRepository {

    public function getListData($request){
        $columns = array( 
            1 =>'name',
            2=> 'email',
            3=> 'created_at',
        );
        $limit = $request->input('length') ? $request->input('length') : "10";
        $start = $request->input('start') ? $request->input('start') : "0";
        $order = empty($request->input('order.0.column')) ? 'created_at' : $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir') ? $request->input('order.0.dir') : 'desc';
        $data = User::offset($start)->limit($limit);
        if(empty($request->input('order.0.column'))){
            $data->orderBy('created_at','desc');
        }else{
            $data->orderBy($order,$dir);
        }
        $totalData = new User;
        if(!empty($request->search['value'])) {
            $data->where('name', "like", "%" . $request->search['value'] . "%");
            $totalData->where('name', "like", "%" . $request->search['value'] . "%");
            $totalData = count($totalData->pluck('id')->toArray());
        }else{
            $totalData = count($totalData->pluck('id')->toArray());
        }
        $totalFiltered = count($data->pluck('id')->toArray());
        $dtTable = Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('path_image', function($row){
                        if(!empty($row['image'])){
                            $urlPath = url('image/user').'/'.$row['image'];
                        }else{
                            $urlPath = '';
                        }
                        return $urlPath;
                    })
                    ->addColumn('created_at', function($row){
     
                           $tgl = $row['created_at']->isoFormat('DD/MM/YYYY H:m:s');
       
                            return $tgl;
                    })
                    ->setTotalRecords($totalFiltered)
                    ->setFilteredRecords($totalData)
                    ->rawColumns(['action','created_at','path_image'])
                    ->make(true);
        return $dtTable;
    }

    public function getUser($id){
        $query = User::firstWhere('id',$id);
        $query->path = url('image/user').'/'.$query->image;
        return $query;
    }

    public function saveData($request,$id=null){
        if(empty($id)){
            $query = new User;
            $rl['name'] = 'min:3|required';
            $rl['email'] = 'required|unique:users,email';
        }else{
            $query = User::where('id',$id)->first();
            $rl['name'] = 'min:3|required';
            $rl['email'] = 'required|unique:users,email,'.$query->id;
        }

        $rules = [
            'name'                  => $rl['name'],
            'email'                  => $rl['email'],
        ];

        $messages = [
            'name.min'              => 'Nama produk minimal 3 karakter',
            'name.required'          => 'Nama produk wajib di isi',
            'email.unique'            => 'Email sudah ada',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return ['errorForm',$validator->errors()];
        }

        if($request->image != 'undefined' && !empty($request->image)){
            $path_img = public_path('image/user');
            if (!File::isDirectory($path_img)) {
                File::makeDirectory($path_img,0777,true);
            }
            if($query->image != null){
                if(!empty($id)){
                    @unlink($path_img.'/'.$query->image);
                }
            }
            $fileName = 'img_'.time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move($path_img, $fileName);
            $query->image = $fileName;
        }

        $query->name = $request->name;
        $query->email = $request->email;
        $query->password = Hash::make($request->password);
        $query->save();

        return ['sukses'];
    }

    public function destroyData($id){
        $delQuery = User::where('id',$id)->first();
        $path_img = public_path('image/user');
        if($delQuery->image != null){
            if(!empty($id)){
                @unlink($path_img.'/'.$delQuery->image);
            }
        }
        $delQuery->delete();
        return ['sukses'];
    }
}