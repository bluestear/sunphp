<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-13 15:07:38
 * @LastEditors: light
 * @LastEditTime: 2023-03-20 16:44:03
 * @Description: SonLight Tech版权所有
 */
// 这是系统自动生成的公共文件


function jsonResult($status, $message, $data)
{
	$result = array(
		'status' => $status,
		'message' => $message,
		'data' => $data,
	);
	header('Content-Type:application/json');
	return json($result);
}