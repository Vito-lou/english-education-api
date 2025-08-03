-- 插入机构数据
INSERT INTO institutions (
    name, code, description, contact_person, contact_phone, contact_email, 
    address, business_license, business_hours, settings, status, established_at, 
    created_at, updated_at
) VALUES (
    '英语教育培训中心',
    'EETC001',
    '专业的英语教育培训机构，致力于提供高质量的英语教学服务',
    '张校长',
    '13800138000',
    'admin@eetc.com',
    '北京市朝阳区教育大厦1-3层',
    'BL123456789',
    '{"monday":["09:00","21:00"],"tuesday":["09:00","21:00"],"wednesday":["09:00","21:00"],"thursday":["09:00","21:00"],"friday":["09:00","21:00"],"saturday":["09:00","18:00"],"sunday":["09:00","18:00"]}',
    '{"max_class_size":12,"booking_advance_days":7,"cancellation_hours":24}',
    'active',
    '2020-01-01',
    NOW(),
    NOW()
);

-- 获取刚插入的机构ID
SET @institution_id = LAST_INSERT_ID();

-- 插入朝阳校区
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    manager_name, manager_phone, address, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    NULL,
    '朝阳校区',
    'CAMPUS_CY',
    'campus',
    '主校区，位于朝阳区教育大厦',
    '李主任',
    '13800138001',
    '北京市朝阳区教育大厦1-3层',
    1,
    'active',
    NOW(),
    NOW()
);

SET @chaoyang_campus_id = LAST_INSERT_ID();

-- 插入教学部
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    manager_name, manager_phone, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    @chaoyang_campus_id,
    '教学部',
    'DEPT_TEACH',
    'department',
    '负责教学管理和课程安排',
    '王老师',
    '13800138002',
    1,
    'active',
    NOW(),
    NOW()
);

SET @teaching_dept_id = LAST_INSERT_ID();

-- 插入教室A
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    capacity, facilities, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    @teaching_dept_id,
    '教室A',
    'ROOM_A',
    'classroom',
    '多媒体教室，适合小班教学',
    12,
    '["投影仪","白板","音响","空调"]',
    1,
    'active',
    NOW(),
    NOW()
);

-- 插入教室B
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    capacity, facilities, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    @teaching_dept_id,
    '教室B',
    'ROOM_B',
    'classroom',
    '标准教室，适合中班教学',
    16,
    '["投影仪","白板","音响"]',
    2,
    'active',
    NOW(),
    NOW()
);

-- 插入销售部
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    manager_name, manager_phone, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    @chaoyang_campus_id,
    '销售部',
    'DEPT_SALES',
    'department',
    '负责招生和客户服务',
    '赵经理',
    '13800138003',
    2,
    'active',
    NOW(),
    NOW()
);

-- 插入海淀校区
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    manager_name, manager_phone, address, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    NULL,
    '海淀校区',
    'CAMPUS_HD',
    'campus',
    '分校区，位于海淀区',
    '陈主任',
    '13800138004',
    '北京市海淀区学院路',
    2,
    'active',
    NOW(),
    NOW()
);

SET @haidian_campus_id = LAST_INSERT_ID();

-- 插入海淀教学部
INSERT INTO departments (
    institution_id, parent_id, name, code, type, description, 
    manager_name, manager_phone, sort_order, status, created_at, updated_at
) VALUES (
    @institution_id,
    @haidian_campus_id,
    '教学部',
    'DEPT_TEACH_HD',
    'department',
    '海淀校区教学部',
    '刘老师',
    '13800138005',
    1,
    'active',
    NOW(),
    NOW()
);

-- 更新测试用户，分配到机构
UPDATE users 
SET institution_id = @institution_id, 
    department_id = @teaching_dept_id,
    updated_at = NOW()
WHERE email = 'admin@example.com';

SELECT 'Test data inserted successfully!' as message;
