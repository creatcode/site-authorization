## 项目结构
### 主要文件和目录
- `LICENSE`: 包含 Apache 2.0 许可证的详细条款。
- `src/`: 源代码目录，包含以下主要文件：
  - `register.php`: 用于站点注册的脚本，处理注册请求、与服务器通信并保存授权信息。
  - `config.php`: 配置文件，定义了系统的基本配置信息。
  - `common.php`: 公共脚本，处理应用初始化、检查授权等操作。
  - `dispatch_jump.tpl`: 模板文件，用于显示系统消息。
- `composer.json`: 项目依赖管理文件，定义了项目的名称、类型、依赖和自动加载规则。

## 配置说明
在开始使用系统之前，需要在 `application/extra/cloud.php` 配置文件中填写完整的配置信息，包括 `url`、`version` 和 `type` 等。
