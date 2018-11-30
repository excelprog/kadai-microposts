<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Micropost;

class FavoriteController extends Controller
{
    public function store(Request $request,$id)
    {    
        //ログインユーザ（自分）が、投稿をお気に入りに登録する
        \Auth::user()->favorite($id);
        return redirect()->back();
    }
    
    public function destroy($id)
    {
        //ログインユーザ（自分）が、投稿をお気に入りから削除する
        \Auth::user()->unfavorite($id);
        return redirect()->back();
    }
    
    public function showFavorites($id)
    {
        //お気に入りに登録している投稿の一覧を取得してviewに投げる
        $user = User::find($id);
        $microposts = $user->favorite_microposts()->paginate(10);
        
        $data = [
            'user' => $user,
            'microposts' => $microposts
        ];
        
        $data += $this->counts($user);
        
        return view('users.favorite', $data);
    }
}
