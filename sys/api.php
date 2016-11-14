<?php

include 'inc/start.php';

if (empty($_POST['requests']) || !is_string($_POST['requests'])) {
    header('Content-type: application/json', true, 500);
    exit;
}

$requests = json_decode($_POST['requests'], true);
$responses = array();

foreach ($requests AS $key => $request_param) {
    $response = $responses[$key] = new api_response();
    try {
        $request = new api_request($request_param);

        $module = $request->module;
        $method = $request->method;

        // проверяем, что необходимый модуль (класс) существует
        if (!class_exists($module)) {
            throw new Exception('api_controller "' . $module . '" not found');
        }

        // проверяем, что класс реализует интерфейс api_controller
        if (!in_array('api_controller', class_implements($module))) {
            throw new Exception('Class "' . $module . '" does not implement interface "api_controller"');
        }

        $reflection = new ReflectionClass($module);

        // проверяем, что у класса имеется необходимый метод
        $reflection->getMethod($method);
        $response->data = call_user_func(array($module, $method), $request->data);
    } catch (ApiException $e) {
        $response->error = $e;
    } catch (Exception $e) {
        $response->error = $e->getMessage();
    }
}

header('Content-type: application/json', true, 200);
echo json_encode($responses);
