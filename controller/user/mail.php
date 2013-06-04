<?php


	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});

	$info = $user->get($user_id);
	$code = $user->mailCodeCreat($user_id);
	$html = "<a href=\"http://monitor.secon.me/mail-verify?code={$code}&mail={$info['mail']}\" target=\"_blank\">http://monitor.secon.me/mail-verify?code={$code}&mail={$info['mail']}</a>";
	send_mail($info['mail'], '邮箱验证', $html);

	json(true, '发送成功');



?>