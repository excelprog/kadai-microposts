<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    //Userのインスタンスが自分のMicropostsを取得することができる
    public function microposts(){
        return $this->hasMany(Micropost::class);
    }
    
    //Userのインスタンスがフォローしているusersを取得する
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    //userのことをフォローしているuserを取得する
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userId);
        //自分自身ではないかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            //既にフォローしていれば何もしない
            return false;
        } else {
            //未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 自分自身ではないかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    //タイムライン用の投稿を取得するメソッド
    public function feed_microposts()
    {
        //userがフォローしているuserのidの配列を取得
        $follow_user_ids = $this->followings()-> pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        //micropostsテーブルのuser_idカラムで、idの配列を含む場合にすべて取得してreturn
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    //Userがお気に入りに登録している投稿を取得する
    public function favorite_microposts()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    public function favorite($micropostId)
    {
        //すでにお気に入れているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            //すでにお気に入りに登録していれば何もしない
            return false;
        } else {
            //まだお気に入りに登録してなければお気に入りに入れる
            $this->favorite_microposts()->attach($micropostId);
            return true;
        }
    }
    
    public function unfavorite($micropostId)
    {
        //すでにお気に入りにいれているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            //すでにお気に入りに登録してるなら外す
            $this->favorite_microposts()->detach($micropostId);
            return true;
        } else {
            return false;
        }
    }
    
    //その投稿がすでにお気に入りに入れているかを確認する
    public function is_favorite($micropostId)
    {
        return $this->favorite_microposts()->where('micropost_id', $micropostId)->exists();
    }
    
}
