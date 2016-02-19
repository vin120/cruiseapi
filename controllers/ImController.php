<?php

namespace app\controllers;


use Yii;
use app\models\ImFriend;
use app\models\ImGroup;
use app\models\ImGroupMember;
use app\models\ImInvitation;
use app\models\ImMember;
use yii\base\Object;

class ImController extends MyActiveController
{
	/**
	 * 1注册Im会员
	 */  
	public function actionRegister()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$name = isset($_POST['name']) ? $_POST['name'] : '';
		$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
		$icon = isset($_POST['icon']) ? $_POST['icon'] : '';
		$response = array();
		if(!empty($member_id) && !empty($name) && !empty($icon))
		{
			$imMember = ImMember::find()->where(['member_id' => $member_id])->one(); 
			if(empty($imMember)){
				$imMember = new ImMember();
				$imMember->member_id = $member_id;
				$imMember->name = $name;
				$imMember->signature = $signature;
				$imMember->icon = $icon;
				$imMember->update_time = date("Y-m-d H:i:s",time());
					
				$bool_state = $imMember->save();
				if($bool_state){
					$response['data'] = 1;
				}else{
					$response['data'] = -1;
				}
			}else {
				$response['data'] = 1;
			}
			
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、name、icon can not be empty','value'=>$_POST);
		}
		return $response;
	}
	
	/**
	 * 2获取Im会员信息
	 */
	public function actionGetmemberinfo()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
	
		$response = array();
	
		if(!empty($member_id)){
			$sql ='SELECT member_id,name,signature,icon FROM vcos_im_member WHERE member_id = \''.$member_id.'\'';
	
			$member = Yii::$app->db->createCommand($sql)->queryOne();
	
			$response['data'] = $member;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'member_id can not be empty','value'=>$_POST);
		}
	
		return  $response;
	}
	/**
	 * 3更新Im会员
	 */
	public function actionUpdatemember()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$name = isset($_POST['name']) ? $_POST['name'] : '';
		$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
		$icon = isset($_POST['icon']) ? $_POST['icon'] : '';
		
		$response = array();
		if(!empty($member_id) && !empty($name) && !empty($icon))
		{
			$imMember = ImMember::find()->where(['member_id' => $member_id])->one(); 
			if(!empty($name)){
				$imMember->name = $name;
			}
			if(!empty($signature)){
				$imMember->signature = $signature;
			}
			if(!empty($icon)){
				$imMember->icon = $icon;
			}
			$imMember->update_time = date("Y-m-d H:i:s",time());
			$bool_state = $imMember->save();
			if($bool_state){
				$response['data'] = 1;
			}else{
				$response['data'] = -1;
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、name、icon can not be empty','value'=>$_POST);
		}
		return $response;
	}
	/**
	 * 4添加好友
	 */
	public function actionAddmyfriend()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$member_name = isset($_POST['member_name']) ? $_POST['member_name'] : '';
		
		$friend_id = isset($_POST['friend_id']) ? $_POST['friend_id'] : '';
		$friend_name = isset($_POST['friend_name']) ? $_POST['friend_name'] : '';
		
		$response = array();
		if(!empty($member_id) && !empty($member_name) && !empty($friend_id) && !empty($friend_name))
		{
			$m_friend = ImFriend::find()->where(['member_id'=>$member_id,'friend_id'=>$friend_id])->all();
			if(empty($m_friend)){
				$imFriend = new ImFriend();
				$imFriend->member_id = $member_id;
				$imFriend->friend_id = $friend_id;
				$imFriend->friend_name = $friend_name;
				$imFriend->add_time = date('Y-m-d H:i:s');
				$m_result = $imFriend->save();
				$response['data'] = 1;
			}else{
				$response['data'] = -1;
			}
			
			$f_friend = ImFriend::find()->where(['member_id'=>$friend_id,'friend_id'=>$member_id])->all();
			if(empty($f_friend)){
				$imFriend = new ImFriend();
				$imFriend->member_id = $friend_id;
				$imFriend->friend_id = $member_id;
				$imFriend->friend_name = $member_name;
				$imFriend->add_time = date('Y-m-d H:i:s');
				$f_result = $imFriend->save();
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、member_name、friend_id、friend_name can not be empty','value'=>$_POST);
		}
		return $response;
	}
	/**
	 * 5获取我的好友
	 */
	public function actionGetmyfriend()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		
		$response = array();
		
		if(!empty($member_id)){
			$sql ='SELECT a.friend_id,a.friend_name,b.signature,b.icon FROM vcos_im_friend a,vcos_im_member b 
					WHERE a.friend_id=b.member_id AND a.member_id = \''.$member_id.'\'';
		
			$friend_array = Yii::$app->db->createCommand($sql)->queryAll();
				
			$response['data'] = $friend_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'member_id can not be empty','value'=>$_POST);
		}
		
		return  $response;
	}
	
	/**
	 * 6创建群
	 */
	public function actionCreategroup()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$member_name = isset($_POST['member_name']) ? $_POST['member_name'] : '';
		$group_name = isset($_POST['group_name']) ? $_POST['group_name'] : '';
		$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
		$icon = isset($_POST['icon']) ? $_POST['icon'] : '';
	
		$response = array();
		if(!empty($member_id) && !empty($member_name) && !empty($group_name) && !empty($icon))
		{		
			$imGroup = new ImGroup();
			$imGroup->group_name = $group_name;
			$imGroup->signature =$signature;
			$imGroup->icon = $icon;
			$imGroup->create_time = date('Y-m-d H:i:s');
			$imGroup->create_member_id=$member_id;
			
			$result = $imGroup->save();
			if($result){
				$imGroupMember = new ImGroupMember();
				$imGroupMember->member_id=$member_id;
				$imGroupMember->member_group_name=$member_name;
				$imGroupMember->group_id=$imGroup->id;
				$join_bool = $imGroupMember->save();
				if ($join_bool){
					$response['data']['status'] = 1;
				}else{
					$response['error'] = array('error_code'=>1,'message'=>'default join group faile after create group','value'=>$_POST);
				}
				
				$response['data']['groupid'] = $imGroup->id;
			}else{
						
				$response['error'] = array('error_code'=>1,'message'=>'create group faile','value'=>$_POST);
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、member_name、group_name、icon can not be empty','value'=>$_POST);
		}
		return $response;
	}
	/**
	 * 7获取群信息
	 * @return multitype:multitype:number string unknown  multitype:
	 */
	public function actionGetgroupinfo()
	{
		$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
	
		$response = array();
	
		if(!empty($group_id)){
			$sql ='SELECT id as group_id,group_name,signature,icon,create_time,create_member_id FROM vcos_im_group WHERE id='.$group_id;
	
			$group_info = Yii::$app->db->createCommand($sql)->queryOne();
	
			$response['data'] = $group_info;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'group_id can not be empty','value'=>$_POST);
		}
	
	
		return  $response;
	}
	
	/**
	 * 8更新群信息
	 */
	public function actionUpdategroup()
	{
		$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		
		$group_name = isset($_POST['group_name']) ? $_POST['group_name'] : '';
		$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
		$icon = isset($_POST['icon']) ? $_POST['icon'] : '';
	
		$response = array();
		if(!empty($member_id) && !empty($group_id))
		{
			$imGroup = ImGroup::findOne($group_id);
			if(!empty($imGroup)){
// 				if($member_id == $imGroup->create_member_id){
				$imGroup->group_name = $group_name;
				$imGroup->signature =$signature;
				$imGroup->icon = $icon;
				
				$result = $imGroup->save();
				if($result){
					$response['data'] = 1;
				}else{
					$response['data'] = -1;
				}
// 				}else{
// 					$response['error'] = array('error_code'=>401,'message'=>'member no permission to modify','value'=>'member permission');
// 				}
				
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'no group','value'=>$_POST);
			}			
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、group_name、signature、icon can not be empty','value'=>$_POST);
		}
		return $response;
	}
	
	/**
	 * 9获取我的群
	 */
	public function actionGetmygroup()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
	
		$response = array();
	
		if(!empty($member_id)){
				
			$sql ='SELECT a.group_id,b.group_name,b.signature,b.icon FROM vcos_im_group_member a,vcos_im_group b 
					WHERE a.group_id=b.id AND a.member_id = \''.$member_id.'\'';
	
			$room_array = Yii::$app->db->createCommand($sql)->queryAll();
	
			$response['data'] = $room_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'member_id can not be empty','value'=>$_POST);
		}
		return  $response;
	}
	
	/**
	 * 10加入群
	 */
	public function actionJoingroup()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$member_group_name = isset($_POST['member_group_name']) ? $_POST['member_group_name'] : '';
		$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
		
		$response = array();
		if(!empty($member_id) && !empty($member_group_name) && !empty($group_id))
		{
			$m_group = ImGroupMember::find()->where(['member_id'=>$member_id,'group_id'=>$group_id])->all();
			if(empty($m_group)){
				$imGroupMember = new ImGroupMember();
				$imGroupMember->member_id=$member_id;
				$imGroupMember->member_group_name=$member_group_name;
				$imGroupMember->group_id=$group_id;
				$result = $imGroupMember->save();
				$response['data'] = 1;
			}else{
				$response['error'] = array('error_code'=>-1,'message'=>'already joined the group');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、member_group_name、group_id can not be empty','value'=>$_POST);
		}
		return $response;
	}

	/**
	 * 11获取群成员
	 */
	public function actionGetgroupmember()
	{
		$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
		$check_time = isset($_POST['check_time']) ? $_POST['check_time'] : '';
	
		$response = array();
	
		if(!empty($group_id)){
			if(!empty($check_time)){
				$sql ='SELECT a.member_id,a.group_id,b.`name`,a.member_group_name,b.signature,b.icon
					FROM vcos_im_group_member a,vcos_im_member b
					WHERE a.member_id = b.member_id AND b.update_time < \''.$check_time.'\' AND a.group_id ='.$group_id;
			}else{
				$sql ='SELECT a.member_id,a.group_id,b.`name`,a.member_group_name,b.signature,b.icon
					FROM vcos_im_group_member a,vcos_im_member b
					WHERE a.member_id = b.member_id AND a.group_id ='.$group_id;
			}
			
			$group_member_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $group_member_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'group_id can not be empty','value'=>$_POST);
		}
	
		return  $response;
	}
	
	/**
	 * 12发送邀请
	 */
	public function actionAddinvitation()
	{
		$send_from = isset($_POST['send_from']) ? $_POST['send_from'] : '';
		$send_to = isset($_POST['send_to']) ? $_POST['send_to'] : '';
		$content = isset($_POST['content']) ? $_POST['content'] : '';
		$type = isset($_POST['type']) ? $_POST['type'] : '';
		
		$response = array();
		if(!empty($send_from) && !empty($send_to) && !empty($type))
		{
			$imInvitation = new ImInvitation();
			$imInvitation->send_from = $send_from;
			$imInvitation->send_to = $send_to;
			$imInvitation->content = $content;
			$imInvitation->type = $type;
			$imInvitation->invitation_time =  date('Y-m-d H:i:s');
			$result = $imInvitation->save();
			
			if($result){
				$response['data'] = 1;
			}else{
				$response['data'] = -1;
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'send_from、send_to、type can not be empty','value'=>$_POST);
		}
		return $response;
	}
	
	/**
	 * 13获取邀请通知
	 */
	public function actionGetmyinvitation()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : date('Y-m-d H:i:s');
		$end_tiem = isset($_POST['end_time']) ? $_POST['end_time'] : date("Y-m-d H:i:s",strtotime("-7 day"));

		$response = array();
	
		if(!empty($member_id)){
			$sql ='SELECT id,send_from,send_to,content,type,result,invitation_time 
					FROM vcos_im_invitation WHERE (send_from =\''.$member_id.'\' OR send_to =\''.$member_id.'\') AND invitation_time <='.$start_time.' AND invitation_time>= '.$end_tiem;
	
			$invitation_array = Yii::$app->db->createCommand($sql)->queryAll();
	
			$response['data'] = $invitation_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'member_id can not be empty','value'=>$_POST);
		}
	
		return  $response;
	}
	
	/**
	 * 14更新邀请通知
	 */
	public function actionUpdateinvitationresult()
	{
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$reslut = isset($_POST['reslut']) ? $_POST['reslut'] : '';
	
		$response = array();
	
		if(!empty($id) && !empty($reslut))
		{
			$imInvitation = ImInvitation::findOne($id);
			$imInvitation->result = $reslut;
				
			$temp_update = $imInvitation->save();
				
			if($temp_update){
				$response['data'] = 1;
			}else{
				$response['data'] = -1;
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'id、reslut can not be empty','value'=>$_POST);
		}
		return $response;
	}
	
	/**
	 * 15 退出该群
	 */
	
	public function actionQuitgroup()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
// 		$member_group_name = isset($_POST['member_group_name']) ? $_POST['member_group_name'] : '';
		$group_id = isset($_POST['group_id']) ? $_POST['group_id'] : '';
		$response = array();
		if(!empty($member_id) && !empty($group_id))
		{
			$imGroupMember =ImGroupMember::find()->where(['member_id'=>$member_id,'group_id'=>$group_id])->one();
			if($imGroupMember)
			{
				if($imGroupMember->delete())
				{
					$response['data'] = 1;
				}else{
					$response['data'] = -1;
				}
			}
			else
			{
				$response['error'] = array('error_code'=>1,'message'=>'not in this group or already quit thig group');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、member_group_name、group_id can not be empty','value'=>$_POST);
		}
		return $response;
	}
	

	/**
	 * 16删除好友
	 */
	public function actionDelmyfriend()
	{
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		$friend_id = isset($_POST['friend_id']) ? $_POST['friend_id'] : '';
		
		$response = array();
		if(!empty($member_id) && !empty($friend_id))
		{
			
			$m_result = ImFriend::find()->where(['member_id'=>$member_id,'friend_id'=>$friend_id])->one();
			if($m_result){
				$m_result->delete();
				$response['data'] = 1;
			}else{
				$response['data'] = -1;
			}
			
			$f_result = ImFriend::find()->where(['member_id'=>$friend_id,'friend_id'=>$member_id])->one();
			if($f_result){
				$f_result->delete();
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'member_id、friend_id、friend_name can not be empty','value'=>$_POST);
		}
		return $response;
	}
	
	
	
	public function actionSearch()
	{
		$search_name = isset($_POST['name']) ? $_POST['name'] : '';
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : '';
		
		if(!empty($search_name))
		{
			$sql_member ='SELECT member_id,name,signature,icon FROM vcos_im_member WHERE member_id = \''.$search_name.'\' OR name = \''.$search_name.'\'';
			$member_array = Yii::$app->db->createCommand($sql_member)->queryAll();
			
			
			
			$sql_group ='SELECT id as group_id,group_name,signature,icon,create_time,create_member_id FROM vcos_im_group WHERE id= \''.$search_name.'\' OR group_name = \''.$search_name.'\'';
			$group_array = Yii::$app->db->createCommand($sql_group)->queryAll();
			
			

			if(!empty($member_id))
			{
				for($i = 0; $i<count($member_array); $i++)
				{
					$params_member = [':member_id'=>$member_id,':search_name'=>$search_name];
					$sql_member = ' SELECT id FROM vcos_im_friend  WHERE member_id=:member_id AND (friend_id =:search_name OR friend_name =:search_name)';
					$in_friend[$i] = Yii::$app->db->createCommand($sql_member,$params_member)->queryOne();
					if($in_friend[$i]){
						$member_array[$i]['is_friend'] = 1;
					}else{
						$member_array[$i]['is_friend'] = 0;
					}
				}
			

				for($i = 0; $i<count($group_array); $i++)
				{
					$sql_member = ' SELECT id FROM vcos_im_friend WHERE member_id=\''.$member_id.'\' AND (friend_id=\''.$search_name.'\' OR friend_name=\''.$search_name.'\' )';
					$in_friend[$i] = Yii::$app->db->createCommand($sql_member,$params_member)->queryOne();
					$in_group[$i] = Yii::$app->db->createCommand($sql_group,$params_group)->queryOne();
					if($in_group[$i]){
						$group_array[$i]['is_group'] = 1;
					}else{
						$group_array[$i]['is_group'] = 0;
					}
				}

				$response['data']['member'] = $member_array;
				$response['data']['group'] = $group_array;
			}
			else
			{
				$response['error'] = array('error_code'=>1,'message'=>'memeber_id can not empty');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'search value can not be empty','value'=>$_POST);
		}
		
		return $response;
	}
	
	
}