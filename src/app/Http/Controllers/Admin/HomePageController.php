<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePageNotice;
use App\Models\User;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function adminHomePage()
    {
        $info = [
            'trafficTotal' => 666,//总访问数
            'tradfficDay' => 26,//今日当问数
            'tradfficWeekData'=>[
               [
                   "time"=>"05/28",
                    "count"=>0
                ],
                [
                    "time"=>"05/27",
                    "count"=>0
                ],
                [
                    "time"=>"05/26",
                    "count"=>0
                ],
                [
                    "time"=>"05/25",
                    "count"=>0
                ],
                [
                    "time"=>"05/24",
                    "count"=>0
                ],
                [
                    "time"=>"05/23",
                    "count"=>0
                ],
                [
                    "time"=>"05/22",
                    "count"=>0
                ],
                [
                    "time"=>"05/21",
                    "count"=>0
                ],
           ]
        ];

        $info['userTotal'] = User::select()->count();//总注册数

        //本周起始日期
        $beginThisweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
        $endThisweek=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        //上周起始时间
        $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
       // 上月起始时间;
        $beginLastmonth = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
        $endLastmonth = mktime(23,59,59,date("m") ,0,date("Y"));
        //本月起始时间时间
        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));

        //（本周-上周）/上周
        $info['UserThisWeek'] = User::whereBetween('created_at',[$beginThisweek,$endThisweek])->count();
        $info['UserLastWeek'] = User::whereBetween('created_at',[$beginLastweek,$endLastweek])->count();
        //（本月-上月）/上月
        $info['UserThisMonth'] = User::whereBetween('created_at',[$beginThismonth,$endThismonth])->count();
        $info['UserLastMonth'] = User::whereBetween('created_at',[$beginLastmonth,$endLastmonth])->count();

        return formatRet(0, '', $info);
    }

    public function adminNotice()
    {
        $homeNotice = HomePageNotice::with('user')->where('notice_type', 'user_certification_owner')
            ->orWhere('notice_type', 'user_certification_renters')
            ->get();
        return formatRet(0, '', $homeNotice->toArray());
    }

    public function adminUsertable(Request $request)
    {
        $this->validate($request, [
            'created_at_b' => 'required|date_format:Y-m-d',
            'created_at_e' => 'required|date_format:Y-m-d',
        ]);

        $startDateTime = new \DateTime($request->created_at_b);
        $endDateTime = new \DateTime($request->created_at_e);
        $interval = $startDateTime->diff($endDateTime);
        $days = $interval->days;
        $startTime = strtotime($request->created_at_b);

        $inputData =  User::select(app("db")->raw('Date(from_unixtime(created_at)) as date_time,count(id) as num'))
            ->groupBy(app("db")->raw('Date(from_unixtime(created_at))'))
            ->get();

        $inputDataInfo = [];
        foreach ($inputData as $val) {
            $inputDataInfo[$val['date_time']] = $val;
        }

        for ($k = $days; $k >= 0; $k--) {
            $data['data'][$k]['registrationTime'] = date("m/d", ($startTime + $k * 86400));

            $startDate = date("Y-m-d", ($startTime + $k * 86400));

            $data['data'][$k]['registrationCount'] = isset($inputDataInfo[$startDate]['num']) ? $inputDataInfo[$startDate]['num'] : 0;
        }
        $data['data'] = array_values($data['data']);
        return formatRet(0, '', $data);
    }

}