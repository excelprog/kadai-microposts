<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Micropost extends Model
{
    protected $fillable = ['content', 'user_id'];
    
    //MicropostをもつUserは1人である、Micropostのインスタンスが所属している唯一のUserを取得することができる
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    //Micropostをお気に入りに登録しているusersを取得する
    public function favorited_users()
    {
        return $this->belongsToMany(User::class, 'favorites', 'micropost_id', 'user_id')->withTimestamps();
    }
    
}
