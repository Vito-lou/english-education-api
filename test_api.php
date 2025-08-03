<?php

// 简单的API测试脚本
$baseUrl = 'http://english-education-api.test/api';

// 测试机构管理API
function testInstitutionAPI() {
    global $baseUrl;
    
    echo "=== 测试机构管理API ===\n";
    
    // 1. 获取机构列表
    echo "1. 获取机构列表...\n";
    $response = file_get_contents("$baseUrl/v2/institutions");
    if ($response) {
        $data = json_decode($response, true);
        echo "✅ 机构列表获取成功\n";
        echo "响应: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "❌ 机构列表获取失败\n\n";
    }
    
    // 2. 创建机构
    echo "2. 创建机构...\n";
    $institutionData = [
        'name' => '测试机构',
        'code' => 'TEST001',
        'description' => '这是一个测试机构',
        'contact_person' => '测试联系人',
        'contact_phone' => '13800138000',
        'status' => 'active'
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($institutionData)
        ]
    ]);
    
    $response = file_get_contents("$baseUrl/v2/institutions", false, $context);
    if ($response) {
        $data = json_decode($response, true);
        echo "✅ 机构创建成功\n";
        echo "响应: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
        return $data['data']['id'] ?? null;
    } else {
        echo "❌ 机构创建失败\n\n";
        return null;
    }
}

// 测试部门管理API
function testDepartmentAPI($institutionId) {
    global $baseUrl;
    
    if (!$institutionId) {
        echo "❌ 无法测试部门API，机构ID为空\n";
        return;
    }
    
    echo "=== 测试部门管理API ===\n";
    
    // 1. 获取部门列表
    echo "1. 获取部门列表...\n";
    $response = file_get_contents("$baseUrl/v2/departments?institution_id=$institutionId");
    if ($response) {
        $data = json_decode($response, true);
        echo "✅ 部门列表获取成功\n";
        echo "响应: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "❌ 部门列表获取失败\n\n";
    }
    
    // 2. 创建部门
    echo "2. 创建部门...\n";
    $departmentData = [
        'institution_id' => $institutionId,
        'name' => '测试校区',
        'code' => 'TEST_CAMPUS',
        'type' => 'campus',
        'description' => '这是一个测试校区',
        'manager_name' => '测试负责人',
        'status' => 'active'
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($departmentData)
        ]
    ]);
    
    $response = file_get_contents("$baseUrl/v2/departments", false, $context);
    if ($response) {
        $data = json_decode($response, true);
        echo "✅ 部门创建成功\n";
        echo "响应: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "❌ 部门创建失败\n\n";
    }
}

// 运行测试
echo "开始测试机构管理API...\n\n";

try {
    $institutionId = testInstitutionAPI();
    testDepartmentAPI($institutionId);
    
    echo "=== 测试完成 ===\n";
} catch (Exception $e) {
    echo "❌ 测试过程中出现错误: " . $e->getMessage() . "\n";
}
