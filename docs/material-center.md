# 素材中心 - 故事与知识点管理模块

---

### 给 AI 的实现需求说明（增强版）

#### 一、项目背景与目标

当前教育系统的课程-级别-单元结构中，故事内容与单元强耦合，不利于素材复用和智能化扩展。本次重构旨在：

1.  **解耦内容与课程**：建立独立的素材中心，实现故事和知识点的集中化管理。
2.  **支持复杂内容结构**：统一管理短篇故事和长篇分章故事（如《哈利波特》）。
3.  **构建知识图谱**：通过结构化标签系统，将知识点与多个教育体系（K12、剑桥、雅思等）关联，为未来 AI 驱动的智能备课、精准推荐等功能奠定数据基础。

#### 二、详细数据库结构设计

**1. 故事管理模块 (`stories` 表)**
| 字段名 | 类型 | 必填 | 描述 | 示例 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | Auto | 主键 | 1 |
| `title` | VARCHAR(255) | Yes | 故事标题 | The Enormous Turnip |
| `description` | TEXT | No | 故事简介 | A classic tale about... |
| `author` | VARCHAR(100) | No | 作者 | Leo Tolstoy |
| `difficulty_level`| VARCHAR(50) | No | 难度等级 | A1, B2, Grade 5 |
| `cover_image_url` | VARCHAR(255) | No | 封面图链接 | /covers/turnip.jpg |
| **`has_chapters`** | **BOOLEAN** | **Yes** | **核心标志：是否分章节** | `false` (默认) |
| `content` | TEXT | **Conditional** | **故事全文**。仅当 `has_chapters = false` 时，此字段才有效并应填入内容。 | Once upon a time... |
| `created_at` | TIMESTAMP | Auto | 创建时间 | 2023-10-27 08:00:00 |

**2. 故事章节模块 (`story_chapters` 表)**

-   此表为 `has_chapters = true` 的故事服务。
    | 字段名 | 类型 | 必填 | 描述 | 示例 |
    | :--- | :--- | :--- | :--- | :--- |
    | `id` | BIGINT (PK) | Auto | 主键 | 1 |
    | `story_id` | BIGINT (FK) | Yes | **外键，关联 `stories.id`** | 2 |
    | `chapter_number` | INT | Yes | 章节序号 | 1, 2, 17 |
    | `chapter_title` | VARCHAR(255) | Yes | 章节标题 | The Boy Who Lived |
    | `content` | LONGTEXT | Yes | **该章节的完整文本内容** | Mr. and Mrs. Dursley... |
    | `word_count` | INT | No | 本章词数（可自动计算） | 4521 |

**3. 知识点核心模块 (`knowledge_points` 表)**
| 字段名 | 类型 | 必填 | 描述 | 示例 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | Auto | 主键 | 1 |
| `name` | VARCHAR(255) | Yes | 知识点名称 | enormous |
| `type` | ENUM | Yes | **类型：vocabulary, grammar, phrase, sentence_pattern** | vocabulary |
| `definition_en` | TEXT | No | 英文释义 | Very large in size. |
| `definition_cn` | TEXT | No | 中文释义 | 巨大的 |
| `explanation` | TEXT | No | 详细用法解释 | Often used to describe size and scale... |
| `example_sentence` | TEXT | No | 示例句 | They live in an enormous house. |
| `audio_url` | VARCHAR(255) | No | 发音音频链接 | /audio/enormous.mp3 |

**4. 知识标签体系 (`knowledge_tags` 表)**
| 字段名 | 类型 | 必填 | 描述 | 示例 |
| :--- | :--- | :--- | :--- | :--- |
| `id` | BIGINT (PK) | Auto | 主键 | 1 |
| `tag_name` | VARCHAR(100) | Yes | 标签名称 | 译林版-五上-Unit-5 |
| **`tag_system`** | **VARCHAR(50)** | **Yes** | **标签体系: k12, cambridge, ielts, toefl** | k12 |
| `description` | TEXT | No | 标签描述 | 人教版小学英语五年级上册第五单元 |
| `meta` | JSON | No | 扩展元数据 | `{"grade": "5", "publisher": "yilin"}` |

**5. 关联关系表 (系统核心)**
**5.1 故事-知识点关联 (`story_knowledge_relations`)**
| 字段名 | 类型 | 描述 |
| :--- | :--- | :--- |
| `id` | BIGINT (PK) | 主键 |
| `story_id` | BIGINT (FK) | 关联 `stories.id` |
| `knowledge_point_id` | BIGINT (FK) | 关联 `knowledge_points.id` |

**5.2 知识点-标签关联 (`knowledge_point_tags`)**
| 字段名 | 类型 | 描述 |
| :--- | :--- | :--- |
| `id` | BIGINT (PK) | 主键 |
| `knowledge_point_id` | BIGINT (FK) | 关联 `knowledge_points.id` |
| `knowledge_tag_id` | BIGINT (FK) | 关联 `knowledge_tags.id` |

#### 三、后台管理界面功能需求

1.  **素材中心独立菜单**：与“教务中心”平级。
2.  **故事管理界面**：
    -   列表页：显示故事标题、作者、难度、是否有章节。
    -   新增/编辑页：
        -   有 **“是否分章节”** 开关按钮。
        -   如果关闭（默认），显示一个大文本编辑器用于填写 `content`。
        -   如果开启，隐藏`content`编辑器，动态加载 **“章节管理”** 子模块，允许用户新增、编辑、排序章节（章节内容保存在`story_chapters`表）。
3.  **知识库管理界面**：
    -   `knowledge_points` 管理：可增删改查知识点核心信息。
    -   `knowledge_tags` 管理：可增删改查标签。
    -   **标签关联功能**：在编辑知识点时，应有一个界面组件允许管理员从现有标签中选择多个标签与之关联（实际操作的是`knowledge_point_tags`表）。

#### 四、最终总结与交付物

您的描述已非常全面。只需将以上结构化设计提供给 AI 或开发人员，他们就能清晰地理解您的架构意图。

**核心交付物清单：**

1.  **数据库 SQL 建表语句**（包含上述 6 张表）。
2.  **后台管理前端页面**（素材中心、故事管理、知识库管理）。
3.  **后端 API 接口**（用于对以上所有实体的增删改查操作）。

您的这个设计成功地构建了一个高度灵活、可扩展的内容中台，完全支撑得起未来“AI 整合调用”的宏伟目标。这是一个非常专业的架构方案。
