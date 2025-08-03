

CREATE TABLE `wl_edu_activities` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `template_id` varchar(50) NOT NULL,
  `main_image` longtext,
  `activity_title` varchar(50) NOT NULL,
  `mechanism_name` varchar(20) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `rules` longtext NOT NULL,
  `introduce` longtext NOT NULL,
  `other` longtext NOT NULL,
  `is_release` tinyint(4) NOT NULL DEFAULT '2',
  `qrcode` varchar(255) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `parent_can_see` tinyint(4) NOT NULL DEFAULT '1',
  `views_number` int(11) DEFAULT '0',
  `forward_number` int(11) DEFAULT '0',
  `in_website` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_activity_groups`
--

CREATE TABLE `wl_edu_activity_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `activity_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_activity_opuses`
--

CREATE TABLE `wl_edu_activity_opuses` (
  `id` int(11) UNSIGNED NOT NULL,
  `activity_id` int(11) NOT NULL,
  `contents` longtext NOT NULL,
  `name` varchar(100) DEFAULT '0',
  `describe` text,
  `vote_number` int(11) DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_activity_opus_votes`
--

CREATE TABLE `wl_edu_activity_opus_votes` (
  `id` int(11) UNSIGNED NOT NULL,
  `opus_id` int(11) NOT NULL,
  `wechat_user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_activity_statistics`
--

CREATE TABLE `wl_edu_activity_statistics` (
  `id` int(11) UNSIGNED NOT NULL,
  `activity_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `views_number` int(11) DEFAULT '0',
  `forward_number` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_activity_students`
--

CREATE TABLE `wl_edu_activity_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `wechat_user_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `pay_status` tinyint(4) DEFAULT '0',
  `out_trade_no` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `pay_time` int(11) DEFAULT NULL,
  `sex` tinyint(4) DEFAULT NULL,
  `age` tinyint(4) DEFAULT NULL,
  `birth` varchar(20) DEFAULT NULL,
  `school_name` varchar(50) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `group_position` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_menu`
--

CREATE TABLE `wl_edu_admin_menu` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permission` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `wl_edu_admin_menu`
--

INSERT INTO `wl_edu_admin_menu` (`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `permission`, `created_at`, `updated_at`) VALUES
(8, 0, 3, '员工管理', 'fa-users', 'auth/users', NULL, '2020-04-09 07:10:23', '2020-07-14 03:44:03'),
(10, 0, 1, '用户管理', 'fa-user', 'users', NULL, '2020-04-09 07:18:00', '2020-07-14 03:44:03'),
(11, 0, 2, '学校管理', 'fa-bank', 'schools', NULL, '2020-04-09 07:18:16', '2020-07-14 03:44:03'),
(12, 0, 7, '系统设置', 'fa-cogs', 'settings', NULL, '2020-04-09 07:44:29', '2020-09-28 09:49:51'),
(13, 18, 9, '系统授权', 'fa-cloud', 'cloud/auth', NULL, '2020-04-09 07:46:38', '2020-09-28 09:49:52'),
(14, 18, 12, '更新日志', 'fa-file-text', 'cloud/log', NULL, '2020-04-09 07:47:10', '2020-09-28 09:49:52'),
(15, 18, 10, '系统更新', 'fa-cloud-download', 'cloud/update', NULL, '2020-04-10 05:43:24', '2020-09-28 09:49:52'),
(18, 0, 8, '授权更新', 'fa-cloud', '', '', '2020-07-14 03:43:45', '2020-09-28 09:49:51'),
(19, 18, 11, '系统工具', 'fa-wrench', 'cloud/tool', '', '2020-07-14 03:44:35', '2020-09-28 09:49:52'),
(20, 0, 4, '直播管理', 'fa-video-camera', '', '', '2020-09-28 09:47:55', '2020-09-28 09:51:14'),
(21, 20, 5, '直播账单', 'fa-calculator', 'live/order', '', '2020-09-28 09:49:13', '2020-09-28 09:50:26'),
(22, 20, 6, '直播设置', 'fa-cog', 'app/lives', '', '2020-09-28 09:49:40', '2020-09-28 09:50:56'),
(23, 18, 13, '系统日志', 'fa-file', 'logs', '', '2020-10-13 09:12:40', '2020-10-13 09:13:00');

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_operation_log`
--

CREATE TABLE `wl_edu_admin_operation_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `input` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_permissions`
--

CREATE TABLE `wl_edu_admin_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `http_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_path` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_roles`
--

CREATE TABLE `wl_edu_admin_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_role_menu`
--

CREATE TABLE `wl_edu_admin_role_menu` (
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_role_permissions`
--

CREATE TABLE `wl_edu_admin_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_role_users`
--

CREATE TABLE `wl_edu_admin_role_users` (
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_users`
--

CREATE TABLE `wl_edu_admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `wl_edu_admin_users`
--

INSERT INTO `wl_edu_admin_users` (`id`, `username`, `password`, `name`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'weliam', '$2y$10$GN.HfB6XBNZLi7955vm2oOCGXcULoKjDfLeFcYKPTrsHiiVdbFPti', '大耳狐', NULL, 'Hmxe9BBE14PY6I9El1i9XRf8gGUGtJoDyjKEbVCcclVEgUHudSSAj4o90uRj', '2020-09-04 06:15:49', '2020-09-07 09:36:04'),
(2, 'admin', '$2y$10$qhqnF4qF86hFz3Svmhlkxu1dv1jL3bqTmA5fgqQ98CqOVerJCGj3q', 'Administrator1', 'images/1fab5f98bee053cb1842002e5917a5c4_b450eb7c08c0a678b98547c036150a76.jpg', 'kyKSJKsUok48IASCa1K6vzt1hX3HKlWNu12hLFFym6mMzmcwNmIlY4Nng9Zx', '2020-04-09 04:13:32', '2022-01-07 10:45:10');

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_admin_user_permissions`
--

CREATE TABLE `wl_edu_admin_user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_albums`
--

CREATE TABLE `wl_edu_albums` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_release` tinyint(4) DEFAULT '2',
  `content` longtext,
  `user_id` int(11) NOT NULL,
  `views` int(11) DEFAULT '0',
  `student_id` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT '2',
  `music` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_attendance_devices`
--

CREATE TABLE `wl_edu_attendance_devices` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `class_date` date NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `intro` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `course_id` varchar(20) DEFAULT NULL,
  `cover_image` varchar(255) NOT NULL,
  `deduction_status` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_attendance_histories`
--

CREATE TABLE `wl_edu_attendance_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `sign_in_time` int(11) DEFAULT NULL,
  `sign_out_time` int(11) DEFAULT NULL,
  `sign_in_type` tinyint(4) DEFAULT NULL,
  `sign_out_type` tinyint(4) DEFAULT NULL,
  `temperature` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_auditions`
--

CREATE TABLE `wl_edu_auditions` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `schedule_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `class_id` int(11) UNSIGNED NOT NULL DEFAULT '1',
  `time` varchar(50) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `duration` varchar(50) DEFAULT NULL,
  `class_start_time` varchar(30) NOT NULL,
  `class_end_time` varchar(30) NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `classroom_id` int(11) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_bank_cards`
--

CREATE TABLE `wl_edu_bank_cards` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `card_num` varchar(20) NOT NULL,
  `bank` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_call_records`
--

CREATE TABLE `wl_edu_call_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `attend_class_day` date NOT NULL,
  `classroom_id` int(11) DEFAULT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `class_hour` float(8,2) NOT NULL,
  `content` varchar(20) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `verify_class_hour` float(8,2) DEFAULT NULL,
  `class_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL,
  `project_type` varchar(255) NOT NULL,
  `type` tinyint(4) DEFAULT '1',
  `course_id` int(11) DEFAULT '0',
  `schedule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_call_record_students`
--

CREATE TABLE `wl_edu_call_record_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `remarks` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `evaluate_date` int(11) DEFAULT NULL,
  `deduction` float(8,2) NOT NULL DEFAULT '0.00',
  `consume_type` tinyint(4) NOT NULL DEFAULT '1',
  `student_type` tinyint(4) NOT NULL DEFAULT '0',
  `class_hour` float(8,2) DEFAULT '0.00',
  `go_beyond` float(8,2) DEFAULT '0.00',
  `super_status` tinyint(255) DEFAULT '0',
  `is_lock` tinyint(4) DEFAULT '2',
  `tutoring_type` tinyint(4) DEFAULT '0',
  `tutoring_at` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT '0',
  `remedial_remarks` varchar(200) DEFAULT NULL,
  `is_info` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_call_record_teachers`
--

CREATE TABLE `wl_edu_call_record_teachers` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_classrooms`
--

CREATE TABLE `wl_edu_classrooms` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `structures_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_categories`
--

CREATE TABLE `wl_edu_class_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_consumes`
--

CREATE TABLE `wl_edu_class_consumes` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `price_type` tinyint(4) DEFAULT '1',
  `type` tinyint(4) NOT NULL,
  `buy_number` float(8,2) NOT NULL,
  `give_number` float(8,2) DEFAULT NULL,
  `call_record_student_id` int(11) DEFAULT NULL,
  `order_content_id` int(11) NOT NULL,
  `structures_id` int(11) DEFAULT NULL,
  `edit_hour_history_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `operator_time` int(11) DEFAULT NULL,
  `deduction_buy_number` float(8,2) DEFAULT '0.00',
  `deduction_give_number` float(8,2) DEFAULT '0.00',
  `consumes_type` tinyint(4) DEFAULT '1',
  `is_end` tinyint(4) DEFAULT '0',
  `use_type` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_ending_histories`
--

CREATE TABLE `wl_edu_class_ending_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `is_revoke` tinyint(4) DEFAULT '2',
  `student_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `user_school_id` int(10) UNSIGNED NOT NULL,
  `number` float(10,2) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `structures_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_schedules`
--

CREATE TABLE `wl_edu_class_schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `start_time` date NOT NULL,
  `end_time` date DEFAULT NULL,
  `repeat_type` tinyint(4) DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `week_num` tinyint(4) DEFAULT NULL,
  `classroom_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT '0',
  `class_start_time` varchar(20) DEFAULT NULL,
  `class_end_time` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_schedules_students`
--

CREATE TABLE `wl_edu_class_schedules_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `from_schedule_id` int(11) NOT NULL DEFAULT '0',
  `student_id` int(11) NOT NULL,
  `to_schedule_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_schedule_adjustments`
--

CREATE TABLE `wl_edu_class_schedule_adjustments` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `schedules_id` int(11) NOT NULL,
  `start_time` date NOT NULL,
  `end_time` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_schedule_teachers`
--

CREATE TABLE `wl_edu_class_schedule_teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_teachers`
--

CREATE TABLE `wl_edu_class_teachers` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_class_times`
--

CREATE TABLE `wl_edu_class_times` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_collection_records`
--

CREATE TABLE `wl_edu_collection_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `amount_collected` decimal(10,2) NOT NULL,
  `service_charge` decimal(10,2) DEFAULT NULL,
  `student_name` varchar(50) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `explain` text,
  `out_trade_no` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_costs`
--

CREATE TABLE `wl_edu_costs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_cost_courses`
--

CREATE TABLE `wl_edu_cost_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `cost_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_coupons`
--

CREATE TABLE `wl_edu_coupons` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_invalid` tinyint(4) NOT NULL DEFAULT '1',
  `type` tinyint(4) DEFAULT NULL,
  `number` float(10,2) DEFAULT NULL,
  `gift_voucher_name` varchar(50) DEFAULT NULL,
  `total` int(255) DEFAULT NULL,
  `validity_type` tinyint(4) NOT NULL,
  `start_time` date DEFAULT NULL,
  `end_time` date DEFAULT NULL,
  `validity_day` int(11) DEFAULT NULL,
  `threshold_type` tinyint(4) DEFAULT NULL,
  `threshold_money` decimal(10,2) DEFAULT NULL,
  `explain` text,
  `poster` varchar(255) DEFAULT NULL,
  `rule` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_courses`
--

CREATE TABLE `wl_edu_courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `color` tinyint(4) NOT NULL DEFAULT '1',
  `leave_cut_hour` tinyint(4) DEFAULT '2',
  `no_come_cut_hour` tinyint(4) DEFAULT '1',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_course_prices`
--

CREATE TABLE `wl_edu_course_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `structures_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_course_price_adds`
--

CREATE TABLE `wl_edu_course_price_adds` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `number` int(11) NOT NULL DEFAULT '0',
  `total_price` decimal(10,2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `course_price_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_edit_hour_histories`
--

CREATE TABLE `wl_edu_edit_hour_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `record_id` int(11) NOT NULL,
  `user_school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluates`
--

CREATE TABLE `wl_edu_evaluates` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `teacher_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `comment` text,
  `record_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluate_interactions`
--

CREATE TABLE `wl_edu_evaluate_interactions` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `project_type` varchar(255) NOT NULL,
  `from_id` int(11) NOT NULL,
  `from_type` varchar(100) NOT NULL,
  `to_id` int(11) DEFAULT NULL,
  `to_type` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluate_score_dims`
--

CREATE TABLE `wl_edu_evaluate_score_dims` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluate_stu_scores`
--

CREATE TABLE `wl_edu_evaluate_stu_scores` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `evaluate_id` int(11) DEFAULT NULL,
  `score` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluate_teacher_scores`
--

CREATE TABLE `wl_edu_evaluate_teacher_scores` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `evaluate_id` int(11) DEFAULT NULL,
  `score_dim_name` varchar(50) DEFAULT NULL,
  `score` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_evaluate_templates`
--

CREATE TABLE `wl_edu_evaluate_templates` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `content` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_exams`
--

CREATE TABLE `wl_edu_exams` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `full_marks` float(8,2) NOT NULL,
  `time` date NOT NULL,
  `source_type` tinyint(4) NOT NULL,
  `is_show` tinyint(4) DEFAULT '1',
  `is_statistics_show` tinyint(4) DEFAULT '1',
  `type` tinyint(4) DEFAULT NULL,
  `highest_score` float(8,2) DEFAULT NULL,
  `average_score` float(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_exam_scores`
--

CREATE TABLE `wl_edu_exam_scores` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `exam_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `score` float(8,2) DEFAULT NULL,
  `comment` varchar(150) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT '2',
  `student_id` int(11) NOT NULL,
  `is_send_message` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_exchange_records`
--

CREATE TABLE `wl_edu_exchange_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `present_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_export_histories`
--

CREATE TABLE `wl_edu_export_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `file_name` varchar(50) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `name_count` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_failed_jobs`
--

CREATE TABLE `wl_edu_failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_follow_up_persons`
--

CREATE TABLE `wl_edu_follow_up_persons` (
  `id` int(20) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `next_time` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `student_id` int(11) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `is_complete` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_forms`
--

CREATE TABLE `wl_edu_forms` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `template_content` longtext NOT NULL,
  `type` tinyint(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `look_num` int(11) DEFAULT '0',
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_form_extensions`
--

CREATE TABLE `wl_edu_form_extensions` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_form_looks`
--

CREATE TABLE `wl_edu_form_looks` (
  `id` int(10) UNSIGNED NOT NULL,
  `form_id` int(11) NOT NULL,
  `created_at` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_form_students`
--

CREATE TABLE `wl_edu_form_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `template_content` longtext NOT NULL,
  `form_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `wechat_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_goods`
--

CREATE TABLE `wl_edu_goods` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT '0',
  `is_enable` tinyint(4) DEFAULT '1',
  `school_id` int(11) NOT NULL,
  `surplus_stock` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_goods_courses`
--

CREATE TABLE `wl_edu_goods_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `goods_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_goods_stock_histories`
--

CREATE TABLE `wl_edu_goods_stock_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `purchase_at` int(11) NOT NULL DEFAULT '0',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `number` int(11) NOT NULL DEFAULT '0',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `goods_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_grades`
--

CREATE TABLE `wl_edu_grades` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_growth_archives`
--

CREATE TABLE `wl_edu_growth_archives` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_show` tinyint(4) DEFAULT '1',
  `type` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `growth_archive_sorts_id` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_growth_archive_projects`
--

CREATE TABLE `wl_edu_growth_archive_projects` (
  `id` int(11) UNSIGNED NOT NULL,
  `growth_archive_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `project_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_growth_archive_sorts`
--

CREATE TABLE `wl_edu_growth_archive_sorts` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_growth_records`
--

CREATE TABLE `wl_edu_growth_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL DEFAULT '1',
  `project_type` varchar(100) NOT NULL,
  `is_collect` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_holidays`
--

CREATE TABLE `wl_edu_holidays` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `tagging` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `start_time` date NOT NULL,
  `end_time` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_homeworks`
--

CREATE TABLE `wl_edu_homeworks` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `deadline` int(11) DEFAULT '0',
  `save_type` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_homework_adds`
--

CREATE TABLE `wl_edu_homework_adds` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `start_day` varchar(20) DEFAULT NULL,
  `end_day` varchar(20) DEFAULT NULL,
  `clock_in_num` int(11) DEFAULT NULL,
  `is_remind` tinyint(4) DEFAULT '1',
  `type` tinyint(4) DEFAULT NULL,
  `homework_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_homework_students`
--

CREATE TABLE `wl_edu_homework_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `homework_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_read` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_homework_stu_contents`
--

CREATE TABLE `wl_edu_homework_stu_contents` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `homework_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `content` longtext,
  `save_type` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_import_histories`
--

CREATE TABLE `wl_edu_import_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `data_type` tinyint(4) NOT NULL,
  `school_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `fail_reason` text,
  `count_num` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remark` text,
  `file_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_income_expenses`
--

CREATE TABLE `wl_edu_income_expenses` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `handling_date` date NOT NULL,
  `remark` text,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_income_expense_accounts`
--

CREATE TABLE `wl_edu_income_expense_accounts` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_income_expense_projects`
--

CREATE TABLE `wl_edu_income_expense_projects` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_show` tinyint(4) DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1',
  `is_enable` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_introduces`
--

CREATE TABLE `wl_edu_introduces` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `promoters_num` int(11) DEFAULT '0',
  `views_num` int(11) DEFAULT '0',
  `payer_num` int(11) DEFAULT '0',
  `project_id` int(11) NOT NULL,
  `project_type` varchar(100) NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `intro` text,
  `invitation` varchar(255) NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `invitees_num` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_labels`
--

CREATE TABLE `wl_edu_labels` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_laravel_sms_log`
--

CREATE TABLE `wl_edu_laravel_sms_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_sent` tinyint(4) NOT NULL DEFAULT '0',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_leaves`
--

CREATE TABLE `wl_edu_leaves` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `student_id` int(11) NOT NULL,
  `content` text,
  `image` text,
  `status` tinyint(4) DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_lives`
--

CREATE TABLE `wl_edu_lives` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `class_date` date NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `intro` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `course_id` varchar(20) DEFAULT NULL,
  `cover_image` varchar(255) NOT NULL,
  `deduction_status` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_live_orders`
--

CREATE TABLE `wl_edu_live_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `live_id` int(11) NOT NULL,
  `order_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` tinyint(4) NOT NULL,
  `number_change_type` tinyint(4) NOT NULL,
  `number_change_value` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `number_before_value` decimal(10,2) NOT NULL,
  `number_after_value` decimal(10,2) NOT NULL,
  `remarks` text,
  `school_id` int(11) NOT NULL,
  `duration` float(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_live_order_operations`
--

CREATE TABLE `wl_edu_live_order_operations` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `live_order_id` int(11) NOT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `info` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_live_students`
--

CREATE TABLE `wl_edu_live_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `live_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_live_student_histories`
--

CREATE TABLE `wl_edu_live_student_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `live_student_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `entry_time` int(11) DEFAULT NULL,
  `leave_time` int(11) DEFAULT NULL,
  `viewing_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_migrations`
--

CREATE TABLE `wl_edu_migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_miss_classes`
--

CREATE TABLE `wl_edu_miss_classes` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '2',
  `record_student_id` int(11) NOT NULL,
  `real_hour` float(8,2) NOT NULL,
  `info` varchar(200) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `is_lock` tinyint(4) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_moredian_devices`
--

CREATE TABLE `wl_edu_moredian_devices` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sn` varchar(128) NOT NULL,
  `deviceId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_moredian_groups`
--

CREATE TABLE `wl_edu_moredian_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `groupId` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_news_delivery_times`
--

CREATE TABLE `wl_edu_news_delivery_times` (
  `id` int(11) UNSIGNED NOT NULL,
  `time` int(11) NOT NULL,
  `type` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `wl_edu_news_delivery_times`
--

INSERT INTO `wl_edu_news_delivery_times` (`id`, `time`, `type`) VALUES
(6485, 1641552828, 1),
(6486, 1641552828, 2);

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_notices`
--

CREATE TABLE `wl_edu_notices` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `content` text NOT NULL,
  `open_parent_confirm` tinyint(4) NOT NULL DEFAULT '1',
  `school_id` int(11) NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_notice_recipients`
--

CREATE TABLE `wl_edu_notice_recipients` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `notice_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_notice_student_confirms`
--

CREATE TABLE `wl_edu_notice_student_confirms` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `notice_id` int(10) UNSIGNED NOT NULL,
  `status` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_notice_student_reads`
--

CREATE TABLE `wl_edu_notice_student_reads` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `notice_id` int(10) UNSIGNED NOT NULL,
  `status` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_operation_records`
--

CREATE TABLE `wl_edu_operation_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exchange_record_id` int(11) NOT NULL,
  `matter` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_orders`
--

CREATE TABLE `wl_edu_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `structures_id` int(11) NOT NULL,
  `receivable` decimal(10,2) NOT NULL,
  `real_price` decimal(10,2) NOT NULL,
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `purchase_at` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `order_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `student_id` int(11) NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `payment_type` tinyint(4) DEFAULT NULL,
  `order_type` tinyint(4) DEFAULT '1',
  `bill_type` tinyint(4) DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1',
  `abolition_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_contents`
--

CREATE TABLE `wl_edu_order_contents` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `buy_number` float(8,2) DEFAULT NULL,
  `buy_start_time` varchar(20) DEFAULT NULL,
  `buy_end_time` varchar(20) DEFAULT NULL,
  `discount` text,
  `give_number` int(11) DEFAULT NULL,
  `signing_price` decimal(10,2) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `set_meal_id` int(11) DEFAULT NULL,
  `course_type` tinyint(4) DEFAULT '0',
  `price_type` tinyint(4) DEFAULT '1',
  `surplus_buy_number` float(8,2) DEFAULT NULL,
  `surplus_give_number` float(8,2) DEFAULT NULL,
  `refund_number` float(8,2) DEFAULT '0.00',
  `is_ending` tinyint(4) DEFAULT '2',
  `hour_due_time` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_histories`
--

CREATE TABLE `wl_edu_order_histories` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `charge_type` tinyint(4) DEFAULT '1',
  `user_id` int(11) DEFAULT NULL,
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `purchase_at` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_history_prices`
--

CREATE TABLE `wl_edu_order_history_prices` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_history_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `charge_type` tinyint(4) NOT NULL DEFAULT '1',
  `bill_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_logs`
--

CREATE TABLE `wl_edu_order_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `operator_name` varchar(30) NOT NULL,
  `event` varchar(30) NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_refunds`
--

CREATE TABLE `wl_edu_order_refunds` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `original_order` int(11) NOT NULL,
  `project_type` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `number` float(8,2) NOT NULL,
  `give_number` float(8,2) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `other_amount` decimal(10,2) DEFAULT '0.00',
  `other_type` tinyint(4) DEFAULT NULL,
  `is_return_warehouse` tinyint(4) DEFAULT NULL,
  `price_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_users`
--

CREATE TABLE `wl_edu_order_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_order_void_logs`
--

CREATE TABLE `wl_edu_order_void_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `user_school_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_parent_notices`
--

CREATE TABLE `wl_edu_parent_notices` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `type` tinyint(4) NOT NULL,
  `image` text NOT NULL,
  `send_mode` text,
  `class_remind_time` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_parent_notice_members`
--

CREATE TABLE `wl_edu_parent_notice_members` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `project_type` varchar(255) NOT NULL,
  `parent_notice_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_payslip_items`
--

CREATE TABLE `wl_edu_payslip_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_presents`
--

CREATE TABLE `wl_edu_presents` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `integral` int(11) NOT NULL,
  `pictures` text,
  `introduce` varchar(128) DEFAULT NULL,
  `convert_num` int(11) NOT NULL,
  `is_on_sale` tinyint(4) NOT NULL DEFAULT '2',
  `is_hot` tinyint(4) NOT NULL DEFAULT '2',
  `sort` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_renewal_reminds`
--

CREATE TABLE `wl_edu_renewal_reminds` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) NOT NULL,
  `structures_id` int(11) NOT NULL,
  `last_remind_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_schools`
--

CREATE TABLE `wl_edu_schools` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `consult_teacher` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `limit_num` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `surplus` decimal(10,2) DEFAULT '0.00',
  `apps` varchar(255) DEFAULT NULL,
  `is_url` tinyint(1) UNSIGNED DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `is_copyright` tinyint(1) UNSIGNED DEFAULT '0',
  `copyright` varchar(255) DEFAULT NULL,
  `is_limit` tinyint(1) UNSIGNED DEFAULT '0',
  `limit_time` int(10) UNSIGNED DEFAULT '0',
  `is_use_main` tinyint(1) UNSIGNED DEFAULT '0',
  `sms_surplus` int(10) UNSIGNED DEFAULT '0',
  `sms_sign` varchar(20) DEFAULT NULL,
  `school_surplus` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_school_classes`
--

CREATE TABLE `wl_edu_school_classes` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `upper_limit` int(11) DEFAULT '0',
  `type` tinyint(4) DEFAULT '1',
  `classroom_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT '1',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `teaching_hours` float(8,2) DEFAULT '1.00',
  `status` tinyint(4) DEFAULT '1',
  `finish_time` int(11) DEFAULT NULL,
  `structures_id` int(11) NOT NULL,
  `class_type` tinyint(4) NOT NULL DEFAULT '1',
  `name_flag` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_school_wechats`
--

CREATE TABLE `wl_edu_school_wechats` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `appid` varchar(32) NOT NULL,
  `appsecret` varchar(32) DEFAULT NULL,
  `refresh_token` varchar(128) DEFAULT NULL,
  `qrcode` varchar(255) NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `cate` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` int(10) UNSIGNED NOT NULL,
  `updated_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_settings`
--

CREATE TABLE `wl_edu_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_settlements`
--

CREATE TABLE `wl_edu_settlements` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `school_id` int(11) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `end_time` varchar(20) NOT NULL,
  `pay_date` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` tinyint(2) DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_settlement_teachers`
--

CREATE TABLE `wl_edu_settlement_teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `user_school_id` int(11) NOT NULL,
  `settlement_id` int(11) NOT NULL,
  `base_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_settlement_teacher_achievements`
--

CREATE TABLE `wl_edu_settlement_teacher_achievements` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `settlement_teacher_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `project_type` varchar(50) DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `summary` decimal(10,2) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `compute_mode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_settlement_teacher_payslips`
--

CREATE TABLE `wl_edu_settlement_teacher_payslips` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `payslip_item_id` int(11) NOT NULL,
  `settlement_teacher_id` int(11) NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_set_meals`
--

CREATE TABLE `wl_edu_set_meals` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_set_meal_relations`
--

CREATE TABLE `wl_edu_set_meal_relations` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `set_meal_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `buy_number` int(11) DEFAULT NULL,
  `give_number` int(11) DEFAULT NULL,
  `discount` text,
  `signing_price` decimal(10,2) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_share_templates`
--

CREATE TABLE `wl_edu_share_templates` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `link_title` varchar(28) DEFAULT NULL,
  `abstract` varchar(39) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `create_user_id` int(11) DEFAULT NULL,
  `operate_user_id` int(11) DEFAULT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_sms_orders`
--

CREATE TABLE `wl_edu_sms_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `school_id` int(10) NOT NULL,
  `order_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `number_change_value` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `number_before_value` int(10) NOT NULL,
  `number_after_value` int(10) NOT NULL,
  `remarks` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_sms_records`
--

CREATE TABLE `wl_edu_sms_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_sent` tinyint(4) NOT NULL DEFAULT '0',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_sms_templates`
--

CREATE TABLE `wl_edu_sms_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ptype` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_sources`
--

CREATE TABLE `wl_edu_sources` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_structures`
--

CREATE TABLE `wl_edu_structures` (
  `id` int(11) UNSIGNED NOT NULL,
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `school_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `address` varchar(128) DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `updated_at` int(11) NOT NULL,
  `created_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_students`
--

CREATE TABLE `wl_edu_students` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `school_id` int(11) NOT NULL,
  `head_portrait` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sex` tinyint(4) DEFAULT '0',
  `grade_id` int(11) DEFAULT '0',
  `main_parent_type` tinyint(4) NOT NULL DEFAULT '0',
  `main_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `spare_parent_type` tinyint(4) DEFAULT '0',
  `spare_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `school_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `follow_up_status` tinyint(4) DEFAULT '1',
  `intention_level` tinyint(4) DEFAULT '1',
  `user_id` int(11) DEFAULT '0',
  `studentid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `academic_supervisor` int(11) DEFAULT '0',
  `age` tinyint(4) DEFAULT NULL,
  `graduation_time` date DEFAULT NULL,
  `invalid_time` int(11) DEFAULT NULL,
  `card_num` varchar(100) DEFAULT NULL,
  `face_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_classes`
--

CREATE TABLE `wl_edu_student_classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `attend_class_type` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_class_hours`
--

CREATE TABLE `wl_edu_student_class_hours` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `last_remind_time` int(11) DEFAULT NULL,
  `class_hour` float(8,2) DEFAULT NULL,
  `class_time` date DEFAULT NULL,
  `buy_class_hour` float(8,2) DEFAULT '0.00',
  `buy_class_start_time` date DEFAULT NULL,
  `buy_class_end_time` date DEFAULT NULL,
  `give_class_hour` float(8,2) DEFAULT '0.00',
  `give_class_time` date DEFAULT NULL,
  `last_class_hour` float(8,2) DEFAULT '0.00',
  `last_class_start_time` date DEFAULT NULL,
  `last_class_end_time` date DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `structures_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_faces`
--

CREATE TABLE `wl_edu_student_faces` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `face_pic` longtext,
  `face_token` varchar(255) DEFAULT NULL,
  `memberId` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_infos`
--

CREATE TABLE `wl_edu_student_infos` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_info_options`
--

CREATE TABLE `wl_edu_student_info_options` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `student_info_id` int(11) NOT NULL,
  `content` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_labels`
--

CREATE TABLE `wl_edu_student_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `label_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_student_student_infos`
--

CREATE TABLE `wl_edu_student_student_infos` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_info_id` int(11) NOT NULL,
  `content` varchar(50) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_subjects`
--

CREATE TABLE `wl_edu_subjects` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `source_type` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_sys_settings`
--

CREATE TABLE `wl_edu_sys_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_teachers`
--

CREATE TABLE `wl_edu_teachers` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `head_portrait` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sex` tinyint(4) DEFAULT '0',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '1',
  `school_id` int(11) NOT NULL,
  `wechat_users_id` int(11) UNSIGNED DEFAULT NULL,
  `nickname` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_teachers_wechats`
--

CREATE TABLE `wl_edu_teachers_wechats` (
  `id` int(11) UNSIGNED NOT NULL,
  `openid` varchar(32) NOT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `sex` tinyint(4) DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_teacher_payslips`
--

CREATE TABLE `wl_edu_teacher_payslips` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `payslip_item_id` int(11) NOT NULL,
  `user_school_id` int(11) NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_teacher_subjects`
--

CREATE TABLE `wl_edu_teacher_subjects` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_users`
--

CREATE TABLE `wl_edu_users` (
  `id` int(20) UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `is_disable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_departments`
--

CREATE TABLE `wl_edu_user_departments` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_school_id` int(11) UNSIGNED NOT NULL,
  `structures_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_model_has_permissions`
--

CREATE TABLE `wl_edu_user_model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_model_has_roles`
--

CREATE TABLE `wl_edu_user_model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_permissions`
--

CREATE TABLE `wl_edu_user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_roles`
--

CREATE TABLE `wl_edu_user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `intro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint(4) DEFAULT '1',
  `school_id` int(11) DEFAULT NULL,
  `collection_config` tinyint(4) DEFAULT '2',
  `leave_config` tinyint(4) DEFAULT '2',
  `permission` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `permission_type_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_permission` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_role_has_permissions`
--

CREATE TABLE `wl_edu_user_role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_user_schools`
--

CREATE TABLE `wl_edu_user_schools` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `homework_notice` tinyint(4) NOT NULL DEFAULT '1',
  `comment_notice` tinyint(4) NOT NULL DEFAULT '1',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `is_on_job` tinyint(4) NOT NULL DEFAULT '1',
  `is_main` tinyint(4) NOT NULL DEFAULT '2',
  `sex` tinyint(4) DEFAULT '0',
  `deleted_at` int(11) DEFAULT NULL,
  `base_pay` decimal(10,2) DEFAULT '0.00',
  `bonus` decimal(10,2) DEFAULT '0.00',
  `achievement` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_website_constitutes`
--

CREATE TABLE `wl_edu_website_constitutes` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) NOT NULL,
  `updated_at` int(10) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `cover_image` varchar(255) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '2',
  `intro` varchar(30) DEFAULT '0',
  `school_id` int(11) NOT NULL,
  `details` longtext,
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_wechat_users`
--

CREATE TABLE `wl_edu_wechat_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `openid` varchar(32) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `school_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_wechat_user_students`
--

CREATE TABLE `wl_edu_wechat_user_students` (
  `wechat_user_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `wl_edu_withdrawal_records`
--

CREATE TABLE `wl_edu_withdrawal_records` (
  `id` int(11) UNSIGNED NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `deleted_at` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `service_charge` decimal(10,2) DEFAULT NULL,
  `withdrawal_amount` decimal(10,2) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bank_card_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `complete_time` int(11) DEFAULT NULL,
  `serial_number` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wl_edu_activities`
--
ALTER TABLE `wl_edu_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_id` (`template_id`);

--
-- Indexes for table `wl_edu_activity_groups`
--
ALTER TABLE `wl_edu_activity_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`);

--
-- Indexes for table `wl_edu_activity_opuses`
--
ALTER TABLE `wl_edu_activity_opuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`);

--
-- Indexes for table `wl_edu_activity_opus_votes`
--
ALTER TABLE `wl_edu_activity_opus_votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`opus_id`);

--
-- Indexes for table `wl_edu_activity_statistics`
--
ALTER TABLE `wl_edu_activity_statistics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`);

--
-- Indexes for table `wl_edu_activity_students`
--
ALTER TABLE `wl_edu_activity_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_grade_id` (`grade_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_wechat_user_id` (`wechat_user_id`),
  ADD KEY `idx_activity_id` (`activity_id`),
  ADD KEY `idx_group_id` (`group_id`);

--
-- Indexes for table `wl_edu_admin_menu`
--
ALTER TABLE `wl_edu_admin_menu`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_operation_log`
--
ALTER TABLE `wl_edu_admin_operation_log`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `wl_edu_admin_operation_log_user_id_index` (`user_id`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_permissions`
--
ALTER TABLE `wl_edu_admin_permissions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `wl_edu_admin_permissions_name_unique` (`name`) USING BTREE,
  ADD UNIQUE KEY `wl_edu_admin_permissions_slug_unique` (`slug`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_roles`
--
ALTER TABLE `wl_edu_admin_roles`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `wl_edu_admin_roles_name_unique` (`name`) USING BTREE,
  ADD UNIQUE KEY `wl_edu_admin_roles_slug_unique` (`slug`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_role_menu`
--
ALTER TABLE `wl_edu_admin_role_menu`
  ADD KEY `wl_edu_admin_role_menu_role_id_menu_id_index` (`role_id`,`menu_id`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_role_permissions`
--
ALTER TABLE `wl_edu_admin_role_permissions`
  ADD KEY `wl_edu_admin_role_permissions_role_id_permission_id_index` (`role_id`,`permission_id`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_role_users`
--
ALTER TABLE `wl_edu_admin_role_users`
  ADD KEY `wl_edu_admin_role_users_role_id_user_id_index` (`role_id`,`user_id`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_users`
--
ALTER TABLE `wl_edu_admin_users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `wl_edu_admin_users_username_unique` (`username`) USING BTREE;

--
-- Indexes for table `wl_edu_admin_user_permissions`
--
ALTER TABLE `wl_edu_admin_user_permissions`
  ADD KEY `wl_edu_admin_user_permissions_user_id_permission_id_index` (`user_id`,`permission_id`) USING BTREE;

--
-- Indexes for table `wl_edu_albums`
--
ALTER TABLE `wl_edu_albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_attendance_devices`
--
ALTER TABLE `wl_edu_attendance_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uni_course_id` (`course_id`);

--
-- Indexes for table `wl_edu_attendance_histories`
--
ALTER TABLE `wl_edu_attendance_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_auditions`
--
ALTER TABLE `wl_edu_auditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_classroom_id` (`classroom_id`);

--
-- Indexes for table `wl_edu_bank_cards`
--
ALTER TABLE `wl_edu_bank_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_call_records`
--
ALTER TABLE `wl_edu_call_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_classroom_id` (`classroom_id`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_schedule_id` (`schedule_id`);

--
-- Indexes for table `wl_edu_call_record_students`
--
ALTER TABLE `wl_edu_call_record_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tutoring_type` (`tutoring_type`),
  ADD KEY `idx_record_id` (`record_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_call_record_teachers`
--
ALTER TABLE `wl_edu_call_record_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_record_id` (`record_id`);

--
-- Indexes for table `wl_edu_classrooms`
--
ALTER TABLE `wl_edu_classrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_structures_id` (`structures_id`);

--
-- Indexes for table `wl_edu_class_categories`
--
ALTER TABLE `wl_edu_class_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_class_consumes`
--
ALTER TABLE `wl_edu_class_consumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_order_content_id` (`order_content_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_call_record_student_id` (`call_record_student_id`),
  ADD KEY `idx_structures_id` (`structures_id`),
  ADD KEY `idx_edit_hour_history_id` (`edit_hour_history_id`),
  ADD KEY `idx_operator_id` (`operator_id`);

--
-- Indexes for table `wl_edu_class_ending_histories`
--
ALTER TABLE `wl_edu_class_ending_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_structures_id` (`structures_id`),
  ADD KEY `idx_user_school_id` (`user_school_id`);

--
-- Indexes for table `wl_edu_class_schedules`
--
ALTER TABLE `wl_edu_class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_classroom_id` (`classroom_id`);

--
-- Indexes for table `wl_edu_class_schedules_students`
--
ALTER TABLE `wl_edu_class_schedules_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_from_schedule_id` (`from_schedule_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_to_schedule_id` (`to_schedule_id`);

--
-- Indexes for table `wl_edu_class_schedule_adjustments`
--
ALTER TABLE `wl_edu_class_schedule_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedules_id` (`schedules_id`);

--
-- Indexes for table `wl_edu_class_schedule_teachers`
--
ALTER TABLE `wl_edu_class_schedule_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`schedule_id`),
  ADD KEY `idx_school_id` (`teacher_id`);

--
-- Indexes for table `wl_edu_class_teachers`
--
ALTER TABLE `wl_edu_class_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_class_id` (`class_id`);

--
-- Indexes for table `wl_edu_class_times`
--
ALTER TABLE `wl_edu_class_times`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_collection_records`
--
ALTER TABLE `wl_edu_collection_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_out_trade_no` (`out_trade_no`);

--
-- Indexes for table `wl_edu_costs`
--
ALTER TABLE `wl_edu_costs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_cost_courses`
--
ALTER TABLE `wl_edu_cost_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`cost_id`),
  ADD KEY `idx_course_id` (`course_id`);

--
-- Indexes for table `wl_edu_coupons`
--
ALTER TABLE `wl_edu_coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_courses`
--
ALTER TABLE `wl_edu_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_school_id` (`school_id`);

--
-- Indexes for table `wl_edu_course_prices`
--
ALTER TABLE `wl_edu_course_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_structures_id` (`structures_id`),
  ADD KEY `idx_course_id` (`course_id`);

--
-- Indexes for table `wl_edu_course_price_adds`
--
ALTER TABLE `wl_edu_course_price_adds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_price_id` (`course_price_id`);

--
-- Indexes for table `wl_edu_edit_hour_histories`
--
ALTER TABLE `wl_edu_edit_hour_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`user_school_id`),
  ADD KEY `idx_record_id` (`record_id`);

--
-- Indexes for table `wl_edu_evaluates`
--
ALTER TABLE `wl_edu_evaluates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_record_id` (`record_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_evaluate_interactions`
--
ALTER TABLE `wl_edu_evaluate_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_from_id` (`from_id`),
  ADD KEY `idx_to_id` (`to_id`);

--
-- Indexes for table `wl_edu_evaluate_score_dims`
--
ALTER TABLE `wl_edu_evaluate_score_dims`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_evaluate_stu_scores`
--
ALTER TABLE `wl_edu_evaluate_stu_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evaluate_id` (`evaluate_id`);

--
-- Indexes for table `wl_edu_evaluate_teacher_scores`
--
ALTER TABLE `wl_edu_evaluate_teacher_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evaluate_id` (`evaluate_id`);

--
-- Indexes for table `wl_edu_evaluate_templates`
--
ALTER TABLE `wl_edu_evaluate_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_exams`
--
ALTER TABLE `wl_edu_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `wl_edu_exam_scores`
--
ALTER TABLE `wl_edu_exam_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_id` (`exam_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_exchange_records`
--
ALTER TABLE `wl_edu_exchange_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_export_histories`
--
ALTER TABLE `wl_edu_export_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_failed_jobs`
--
ALTER TABLE `wl_edu_failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_follow_up_persons`
--
ALTER TABLE `wl_edu_follow_up_persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_forms`
--
ALTER TABLE `wl_edu_forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_form_extensions`
--
ALTER TABLE `wl_edu_form_extensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_form_id` (`form_id`);

--
-- Indexes for table `wl_edu_form_looks`
--
ALTER TABLE `wl_edu_form_looks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_form_id` (`form_id`);

--
-- Indexes for table `wl_edu_form_students`
--
ALTER TABLE `wl_edu_form_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_form_id` (`form_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_wechat_user_id` (`wechat_user_id`);

--
-- Indexes for table `wl_edu_goods`
--
ALTER TABLE `wl_edu_goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_goods_courses`
--
ALTER TABLE `wl_edu_goods_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`goods_id`),
  ADD KEY `idx_course_id` (`course_id`);

--
-- Indexes for table `wl_edu_goods_stock_histories`
--
ALTER TABLE `wl_edu_goods_stock_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_grades`
--
ALTER TABLE `wl_edu_grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_growth_archives`
--
ALTER TABLE `wl_edu_growth_archives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_growth_archive_sorts_id` (`growth_archive_sorts_id`);

--
-- Indexes for table `wl_edu_growth_archive_projects`
--
ALTER TABLE `wl_edu_growth_archive_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_growth_archive_id` (`growth_archive_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_growth_archive_sorts`
--
ALTER TABLE `wl_edu_growth_archive_sorts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_growth_records`
--
ALTER TABLE `wl_edu_growth_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_holidays`
--
ALTER TABLE `wl_edu_holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_homeworks`
--
ALTER TABLE `wl_edu_homeworks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_class_id` (`class_id`);

--
-- Indexes for table `wl_edu_homework_adds`
--
ALTER TABLE `wl_edu_homework_adds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_homework_id` (`homework_id`);

--
-- Indexes for table `wl_edu_homework_students`
--
ALTER TABLE `wl_edu_homework_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_homework_id` (`homework_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_homework_stu_contents`
--
ALTER TABLE `wl_edu_homework_stu_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_homework_id` (`homework_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_import_histories`
--
ALTER TABLE `wl_edu_import_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_income_expenses`
--
ALTER TABLE `wl_edu_income_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`project_id`);

--
-- Indexes for table `wl_edu_income_expense_accounts`
--
ALTER TABLE `wl_edu_income_expense_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_income_expense_projects`
--
ALTER TABLE `wl_edu_income_expense_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_introduces`
--
ALTER TABLE `wl_edu_introduces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_labels`
--
ALTER TABLE `wl_edu_labels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_laravel_sms_log`
--
ALTER TABLE `wl_edu_laravel_sms_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_leaves`
--
ALTER TABLE `wl_edu_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_lives`
--
ALTER TABLE `wl_edu_lives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uni_course_id` (`course_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`);

--
-- Indexes for table `wl_edu_live_orders`
--
ALTER TABLE `wl_edu_live_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_live_id` (`live_id`);

--
-- Indexes for table `wl_edu_live_order_operations`
--
ALTER TABLE `wl_edu_live_order_operations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_live_students`
--
ALTER TABLE `wl_edu_live_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_live_id` (`live_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_live_student_histories`
--
ALTER TABLE `wl_edu_live_student_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_migrations`
--
ALTER TABLE `wl_edu_migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_miss_classes`
--
ALTER TABLE `wl_edu_miss_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_record_student_id` (`record_student_id`);

--
-- Indexes for table `wl_edu_moredian_devices`
--
ALTER TABLE `wl_edu_moredian_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_moredian_groups`
--
ALTER TABLE `wl_edu_moredian_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_news_delivery_times`
--
ALTER TABLE `wl_edu_news_delivery_times`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_notices`
--
ALTER TABLE `wl_edu_notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_notice_recipients`
--
ALTER TABLE `wl_edu_notice_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`notice_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_notice_student_confirms`
--
ALTER TABLE `wl_edu_notice_student_confirms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`student_id`),
  ADD KEY `idx_school_id` (`notice_id`);

--
-- Indexes for table `wl_edu_notice_student_reads`
--
ALTER TABLE `wl_edu_notice_student_reads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`student_id`),
  ADD KEY `idx_school_id` (`notice_id`);

--
-- Indexes for table `wl_edu_operation_records`
--
ALTER TABLE `wl_edu_operation_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_orders`
--
ALTER TABLE `wl_edu_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_structures_id` (`structures_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_order_contents`
--
ALTER TABLE `wl_edu_order_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_id` (`project_type`,`project_id`),
  ADD KEY `idx_project_type` (`project_type`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_price_type` (`price_type`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `wl_edu_order_histories`
--
ALTER TABLE `wl_edu_order_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_order_history_prices`
--
ALTER TABLE `wl_edu_order_history_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_history_id` (`order_history_id`);

--
-- Indexes for table `wl_edu_order_logs`
--
ALTER TABLE `wl_edu_order_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `wl_edu_order_refunds`
--
ALTER TABLE `wl_edu_order_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_order_users`
--
ALTER TABLE `wl_edu_order_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_school_id` (`user_school_id`);

--
-- Indexes for table `wl_edu_order_void_logs`
--
ALTER TABLE `wl_edu_order_void_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_school_id` (`user_school_id`);

--
-- Indexes for table `wl_edu_parent_notices`
--
ALTER TABLE `wl_edu_parent_notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_parent_notice_members`
--
ALTER TABLE `wl_edu_parent_notice_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_notice_id` (`parent_notice_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_payslip_items`
--
ALTER TABLE `wl_edu_payslip_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_presents`
--
ALTER TABLE `wl_edu_presents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_renewal_reminds`
--
ALTER TABLE `wl_edu_renewal_reminds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_structures_id` (`structures_id`);

--
-- Indexes for table `wl_edu_schools`
--
ALTER TABLE `wl_edu_schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_school_classes`
--
ALTER TABLE `wl_edu_school_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_school_deleted` (`name`,`school_id`,`deleted_at`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_classroom_id` (`classroom_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_structures_id` (`structures_id`);

--
-- Indexes for table `wl_edu_school_wechats`
--
ALTER TABLE `wl_edu_school_wechats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sid_aid_cate` (`school_id`,`appid`,`cate`),
  ADD KEY `idx_sid_cate` (`school_id`,`cate`);

--
-- Indexes for table `wl_edu_settings`
--
ALTER TABLE `wl_edu_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_settlements`
--
ALTER TABLE `wl_edu_settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wl_edu_settlement_teachers`
--
ALTER TABLE `wl_edu_settlement_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_settlement_id` (`settlement_id`),
  ADD KEY `idx_user_school_id` (`user_school_id`);

--
-- Indexes for table `wl_edu_settlement_teacher_achievements`
--
ALTER TABLE `wl_edu_settlement_teacher_achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_settlement_teacher_id` (`settlement_teacher_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_settlement_teacher_payslips`
--
ALTER TABLE `wl_edu_settlement_teacher_payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payslip_item_id` (`payslip_item_id`),
  ADD KEY `idx_settlement_teacher_id` (`settlement_teacher_id`);

--
-- Indexes for table `wl_edu_set_meals`
--
ALTER TABLE `wl_edu_set_meals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_set_meal_relations`
--
ALTER TABLE `wl_edu_set_meal_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_set_meal_id` (`set_meal_id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `wl_edu_share_templates`
--
ALTER TABLE `wl_edu_share_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_operate_user_id` (`operate_user_id`),
  ADD KEY `idx_create_user_id` (`create_user_id`);

--
-- Indexes for table `wl_edu_sms_orders`
--
ALTER TABLE `wl_edu_sms_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`);

--
-- Indexes for table `wl_edu_sms_records`
--
ALTER TABLE `wl_edu_sms_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_sms_templates`
--
ALTER TABLE `wl_edu_sms_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_sources`
--
ALTER TABLE `wl_edu_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_structures`
--
ALTER TABLE `wl_edu_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sid_type_delat` (`school_id`,`type`,`deleted_at`);

--
-- Indexes for table `wl_edu_students`
--
ALTER TABLE `wl_edu_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_grade_id` (`grade_id`),
  ADD KEY `idx_main_phone` (`main_phone`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_academic_supervisor` (`academic_supervisor`),
  ADD KEY `idx_age` (`age`),
  ADD KEY `idx_birthday` (`birthday`),
  ADD KEY `idx_follow_up_status` (`follow_up_status`),
  ADD KEY `idx_source_id` (`source_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `wl_edu_student_classes`
--
ALTER TABLE `wl_edu_student_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`student_id`),
  ADD KEY `idx_school_id` (`class_id`);

--
-- Indexes for table `wl_edu_student_class_hours`
--
ALTER TABLE `wl_edu_student_class_hours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_student_course_structures` (`student_id`,`course_id`,`structures_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_structures` (`structures_id`);

--
-- Indexes for table `wl_edu_student_faces`
--
ALTER TABLE `wl_edu_student_faces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `wl_edu_student_infos`
--
ALTER TABLE `wl_edu_student_infos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_student_info_options`
--
ALTER TABLE `wl_edu_student_info_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_info_id` (`student_info_id`);

--
-- Indexes for table `wl_edu_student_labels`
--
ALTER TABLE `wl_edu_student_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_label_id` (`label_id`);

--
-- Indexes for table `wl_edu_student_student_infos`
--
ALTER TABLE `wl_edu_student_student_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_student_info_id` (`student_info_id`),
  ADD KEY `idx_option_id` (`option_id`);

--
-- Indexes for table `wl_edu_subjects`
--
ALTER TABLE `wl_edu_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`);

--
-- Indexes for table `wl_edu_sys_settings`
--
ALTER TABLE `wl_edu_sys_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_key` (`key`);

--
-- Indexes for table `wl_edu_teachers`
--
ALTER TABLE `wl_edu_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_id` (`school_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `wl_edu_teachers_wechats`
--
ALTER TABLE `wl_edu_teachers_wechats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `openid` (`openid`);

--
-- Indexes for table `wl_edu_teacher_payslips`
--
ALTER TABLE `wl_edu_teacher_payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payslip_item_id` (`payslip_item_id`),
  ADD KEY `idx_user_school_id` (`user_school_id`);

--
-- Indexes for table `wl_edu_teacher_subjects`
--
ALTER TABLE `wl_edu_teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_subject` (`teacher_id`,`subject_id`),
  ADD KEY `idx_subject_teacher` (`teacher_id`,`subject_id`);

--
-- Indexes for table `wl_edu_users`
--
ALTER TABLE `wl_edu_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone` (`phone`),
  ADD KEY `idx_teacher_id` (`teacher_id`);

--
-- Indexes for table `wl_edu_user_departments`
--
ALTER TABLE `wl_edu_user_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_user_model_has_permissions`
--
ALTER TABLE `wl_edu_user_model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `wl_edu_user_model_has_roles`
--
ALTER TABLE `wl_edu_user_model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `wl_edu_user_permissions`
--
ALTER TABLE `wl_edu_user_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_user_roles`
--
ALTER TABLE `wl_edu_user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uqe_name_school_id` (`name`,`school_id`);

--
-- Indexes for table `wl_edu_user_role_has_permissions`
--
ALTER TABLE `wl_edu_user_role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `wl_edu_user_role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `wl_edu_user_schools`
--
ALTER TABLE `wl_edu_user_schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_school_id` (`user_id`,`school_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_school_user_id` (`school_id`,`user_id`);

--
-- Indexes for table `wl_edu_website_constitutes`
--
ALTER TABLE `wl_edu_website_constitutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wl_edu_wechat_users`
--
ALTER TABLE `wl_edu_wechat_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_openid` (`openid`),
  ADD KEY `idx` (`school_id`);

--
-- Indexes for table `wl_edu_wechat_user_students`
--
ALTER TABLE `wl_edu_wechat_user_students`
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_wechat_user_id` (`wechat_user_id`);

--
-- Indexes for table `wl_edu_withdrawal_records`
--
ALTER TABLE `wl_edu_withdrawal_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_card_id` (`bank_card_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `wl_edu_activities`
--
ALTER TABLE `wl_edu_activities`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用表AUTO_INCREMENT `wl_edu_activity_groups`
--
ALTER TABLE `wl_edu_activity_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_activity_opuses`
--
ALTER TABLE `wl_edu_activity_opuses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- 使用表AUTO_INCREMENT `wl_edu_activity_opus_votes`
--
ALTER TABLE `wl_edu_activity_opus_votes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44017;

--
-- 使用表AUTO_INCREMENT `wl_edu_activity_statistics`
--
ALTER TABLE `wl_edu_activity_statistics`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- 使用表AUTO_INCREMENT `wl_edu_activity_students`
--
ALTER TABLE `wl_edu_activity_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用表AUTO_INCREMENT `wl_edu_admin_menu`
--
ALTER TABLE `wl_edu_admin_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用表AUTO_INCREMENT `wl_edu_admin_operation_log`
--
ALTER TABLE `wl_edu_admin_operation_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_admin_permissions`
--
ALTER TABLE `wl_edu_admin_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_admin_roles`
--
ALTER TABLE `wl_edu_admin_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_admin_users`
--
ALTER TABLE `wl_edu_admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `wl_edu_albums`
--
ALTER TABLE `wl_edu_albums`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_attendance_devices`
--
ALTER TABLE `wl_edu_attendance_devices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_attendance_histories`
--
ALTER TABLE `wl_edu_attendance_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_auditions`
--
ALTER TABLE `wl_edu_auditions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_bank_cards`
--
ALTER TABLE `wl_edu_bank_cards`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_call_records`
--
ALTER TABLE `wl_edu_call_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2269;

--
-- 使用表AUTO_INCREMENT `wl_edu_call_record_students`
--
ALTER TABLE `wl_edu_call_record_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13997;

--
-- 使用表AUTO_INCREMENT `wl_edu_call_record_teachers`
--
ALTER TABLE `wl_edu_call_record_teachers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2406;

--
-- 使用表AUTO_INCREMENT `wl_edu_classrooms`
--
ALTER TABLE `wl_edu_classrooms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_categories`
--
ALTER TABLE `wl_edu_class_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_consumes`
--
ALTER TABLE `wl_edu_class_consumes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6431;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_ending_histories`
--
ALTER TABLE `wl_edu_class_ending_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_schedules`
--
ALTER TABLE `wl_edu_class_schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_schedules_students`
--
ALTER TABLE `wl_edu_class_schedules_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_schedule_adjustments`
--
ALTER TABLE `wl_edu_class_schedule_adjustments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=707;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_schedule_teachers`
--
ALTER TABLE `wl_edu_class_schedule_teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_teachers`
--
ALTER TABLE `wl_edu_class_teachers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- 使用表AUTO_INCREMENT `wl_edu_class_times`
--
ALTER TABLE `wl_edu_class_times`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- 使用表AUTO_INCREMENT `wl_edu_collection_records`
--
ALTER TABLE `wl_edu_collection_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_costs`
--
ALTER TABLE `wl_edu_costs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_cost_courses`
--
ALTER TABLE `wl_edu_cost_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_coupons`
--
ALTER TABLE `wl_edu_coupons`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_courses`
--
ALTER TABLE `wl_edu_courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- 使用表AUTO_INCREMENT `wl_edu_course_prices`
--
ALTER TABLE `wl_edu_course_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- 使用表AUTO_INCREMENT `wl_edu_course_price_adds`
--
ALTER TABLE `wl_edu_course_price_adds`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- 使用表AUTO_INCREMENT `wl_edu_edit_hour_histories`
--
ALTER TABLE `wl_edu_edit_hour_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluates`
--
ALTER TABLE `wl_edu_evaluates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluate_interactions`
--
ALTER TABLE `wl_edu_evaluate_interactions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluate_score_dims`
--
ALTER TABLE `wl_edu_evaluate_score_dims`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluate_stu_scores`
--
ALTER TABLE `wl_edu_evaluate_stu_scores`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluate_teacher_scores`
--
ALTER TABLE `wl_edu_evaluate_teacher_scores`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `wl_edu_evaluate_templates`
--
ALTER TABLE `wl_edu_evaluate_templates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_exams`
--
ALTER TABLE `wl_edu_exams`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_exam_scores`
--
ALTER TABLE `wl_edu_exam_scores`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_exchange_records`
--
ALTER TABLE `wl_edu_exchange_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_export_histories`
--
ALTER TABLE `wl_edu_export_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `wl_edu_failed_jobs`
--
ALTER TABLE `wl_edu_failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- 使用表AUTO_INCREMENT `wl_edu_follow_up_persons`
--
ALTER TABLE `wl_edu_follow_up_persons`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `wl_edu_forms`
--
ALTER TABLE `wl_edu_forms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `wl_edu_form_extensions`
--
ALTER TABLE `wl_edu_form_extensions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `wl_edu_form_looks`
--
ALTER TABLE `wl_edu_form_looks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `wl_edu_form_students`
--
ALTER TABLE `wl_edu_form_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_goods`
--
ALTER TABLE `wl_edu_goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_goods_courses`
--
ALTER TABLE `wl_edu_goods_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_goods_stock_histories`
--
ALTER TABLE `wl_edu_goods_stock_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `wl_edu_grades`
--
ALTER TABLE `wl_edu_grades`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- 使用表AUTO_INCREMENT `wl_edu_growth_archives`
--
ALTER TABLE `wl_edu_growth_archives`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `wl_edu_growth_archive_projects`
--
ALTER TABLE `wl_edu_growth_archive_projects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_growth_archive_sorts`
--
ALTER TABLE `wl_edu_growth_archive_sorts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- 使用表AUTO_INCREMENT `wl_edu_growth_records`
--
ALTER TABLE `wl_edu_growth_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_holidays`
--
ALTER TABLE `wl_edu_holidays`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_homeworks`
--
ALTER TABLE `wl_edu_homeworks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `wl_edu_homework_adds`
--
ALTER TABLE `wl_edu_homework_adds`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_homework_students`
--
ALTER TABLE `wl_edu_homework_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_homework_stu_contents`
--
ALTER TABLE `wl_edu_homework_stu_contents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `wl_edu_import_histories`
--
ALTER TABLE `wl_edu_import_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- 使用表AUTO_INCREMENT `wl_edu_income_expenses`
--
ALTER TABLE `wl_edu_income_expenses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=399;

--
-- 使用表AUTO_INCREMENT `wl_edu_income_expense_accounts`
--
ALTER TABLE `wl_edu_income_expense_accounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `wl_edu_income_expense_projects`
--
ALTER TABLE `wl_edu_income_expense_projects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- 使用表AUTO_INCREMENT `wl_edu_introduces`
--
ALTER TABLE `wl_edu_introduces`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_labels`
--
ALTER TABLE `wl_edu_labels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_laravel_sms_log`
--
ALTER TABLE `wl_edu_laravel_sms_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- 使用表AUTO_INCREMENT `wl_edu_leaves`
--
ALTER TABLE `wl_edu_leaves`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_lives`
--
ALTER TABLE `wl_edu_lives`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_live_orders`
--
ALTER TABLE `wl_edu_live_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_live_order_operations`
--
ALTER TABLE `wl_edu_live_order_operations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_live_students`
--
ALTER TABLE `wl_edu_live_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_live_student_histories`
--
ALTER TABLE `wl_edu_live_student_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_migrations`
--
ALTER TABLE `wl_edu_migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_miss_classes`
--
ALTER TABLE `wl_edu_miss_classes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_moredian_devices`
--
ALTER TABLE `wl_edu_moredian_devices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_moredian_groups`
--
ALTER TABLE `wl_edu_moredian_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_news_delivery_times`
--
ALTER TABLE `wl_edu_news_delivery_times`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6487;

--
-- 使用表AUTO_INCREMENT `wl_edu_notices`
--
ALTER TABLE `wl_edu_notices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `wl_edu_notice_recipients`
--
ALTER TABLE `wl_edu_notice_recipients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_notice_student_confirms`
--
ALTER TABLE `wl_edu_notice_student_confirms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_notice_student_reads`
--
ALTER TABLE `wl_edu_notice_student_reads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_operation_records`
--
ALTER TABLE `wl_edu_operation_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_orders`
--
ALTER TABLE `wl_edu_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=800;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_contents`
--
ALTER TABLE `wl_edu_order_contents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=836;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_histories`
--
ALTER TABLE `wl_edu_order_histories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=800;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_history_prices`
--
ALTER TABLE `wl_edu_order_history_prices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=787;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_logs`
--
ALTER TABLE `wl_edu_order_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=812;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_refunds`
--
ALTER TABLE `wl_edu_order_refunds`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_users`
--
ALTER TABLE `wl_edu_order_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=549;

--
-- 使用表AUTO_INCREMENT `wl_edu_order_void_logs`
--
ALTER TABLE `wl_edu_order_void_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用表AUTO_INCREMENT `wl_edu_parent_notices`
--
ALTER TABLE `wl_edu_parent_notices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_parent_notice_members`
--
ALTER TABLE `wl_edu_parent_notice_members`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_payslip_items`
--
ALTER TABLE `wl_edu_payslip_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_presents`
--
ALTER TABLE `wl_edu_presents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_renewal_reminds`
--
ALTER TABLE `wl_edu_renewal_reminds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- 使用表AUTO_INCREMENT `wl_edu_schools`
--
ALTER TABLE `wl_edu_schools`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_school_classes`
--
ALTER TABLE `wl_edu_school_classes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- 使用表AUTO_INCREMENT `wl_edu_school_wechats`
--
ALTER TABLE `wl_edu_school_wechats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `wl_edu_settings`
--
ALTER TABLE `wl_edu_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用表AUTO_INCREMENT `wl_edu_settlements`
--
ALTER TABLE `wl_edu_settlements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_settlement_teachers`
--
ALTER TABLE `wl_edu_settlement_teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_settlement_teacher_achievements`
--
ALTER TABLE `wl_edu_settlement_teacher_achievements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_settlement_teacher_payslips`
--
ALTER TABLE `wl_edu_settlement_teacher_payslips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_set_meals`
--
ALTER TABLE `wl_edu_set_meals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_set_meal_relations`
--
ALTER TABLE `wl_edu_set_meal_relations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_share_templates`
--
ALTER TABLE `wl_edu_share_templates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- 使用表AUTO_INCREMENT `wl_edu_sms_orders`
--
ALTER TABLE `wl_edu_sms_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_sms_records`
--
ALTER TABLE `wl_edu_sms_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_sms_templates`
--
ALTER TABLE `wl_edu_sms_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `wl_edu_sources`
--
ALTER TABLE `wl_edu_sources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- 使用表AUTO_INCREMENT `wl_edu_structures`
--
ALTER TABLE `wl_edu_structures`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_students`
--
ALTER TABLE `wl_edu_students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=562;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_classes`
--
ALTER TABLE `wl_edu_student_classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=699;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_class_hours`
--
ALTER TABLE `wl_edu_student_class_hours`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_faces`
--
ALTER TABLE `wl_edu_student_faces`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_infos`
--
ALTER TABLE `wl_edu_student_infos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_info_options`
--
ALTER TABLE `wl_edu_student_info_options`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_labels`
--
ALTER TABLE `wl_edu_student_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_student_student_infos`
--
ALTER TABLE `wl_edu_student_student_infos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_subjects`
--
ALTER TABLE `wl_edu_subjects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- 使用表AUTO_INCREMENT `wl_edu_sys_settings`
--
ALTER TABLE `wl_edu_sys_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `wl_edu_teachers`
--
ALTER TABLE `wl_edu_teachers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- 使用表AUTO_INCREMENT `wl_edu_teachers_wechats`
--
ALTER TABLE `wl_edu_teachers_wechats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_teacher_payslips`
--
ALTER TABLE `wl_edu_teacher_payslips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_teacher_subjects`
--
ALTER TABLE `wl_edu_teacher_subjects`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- 使用表AUTO_INCREMENT `wl_edu_users`
--
ALTER TABLE `wl_edu_users`
  MODIFY `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- 使用表AUTO_INCREMENT `wl_edu_user_departments`
--
ALTER TABLE `wl_edu_user_departments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_user_permissions`
--
ALTER TABLE `wl_edu_user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `wl_edu_user_roles`
--
ALTER TABLE `wl_edu_user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `wl_edu_user_schools`
--
ALTER TABLE `wl_edu_user_schools`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- 使用表AUTO_INCREMENT `wl_edu_website_constitutes`
--
ALTER TABLE `wl_edu_website_constitutes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `wl_edu_wechat_users`
--
ALTER TABLE `wl_edu_wechat_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5564;

--
-- 使用表AUTO_INCREMENT `wl_edu_withdrawal_records`
--
ALTER TABLE `wl_edu_withdrawal_records`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
