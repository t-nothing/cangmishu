<?php
/**
 * Swiper.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequests;


class SwiperController extends Controller
{

    public function list(){

        $arr[] = [
            "id"            =>  1,
            "caption"       =>  "合作 谁是你的菜",
            "link_url"      =>  "",
            "image_url"     =>  resource_path('images/swiper/8e50c65fda145e6dd1bf4fb7ee0fcecc.jpg'),
        ];
        $arr[] = [
            "id"            =>  2,
            "caption"       =>  "活动 美食节",
            "link_url"      =>  "",
            "image_url"     =>  storage_path('images/swiper/65091eebc48899298171c2eb6696fe27.jpg'),
        ];
        $arr[] = [
            "id"            =>  3,
            "caption"       =>  "活动 母亲节",
            "link_url"      =>  "",
            "image_url"     =>  public_path('images/swiper/bff2e49136fcef1fd829f5036e07f116.jpg'),
        ];
        
        return formatRet(0, '', $arr);
      
    }
}