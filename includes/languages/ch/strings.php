<?php
/*
===============================================================
CERBERUS HELPDESK
Version: 2.6.0 
---------------------------------------------------------------
Simplified Chinese Language Resource File
By:
	Flora Su, fsu@sh.symbidia.com
	Jimmy Wu, jwu@sh.symbidia.com
	Tommy Huang, thuang@sh.symbidia.com
===============================================================
*/

// Language Info
define("LANG_NAME","Simplified Chinese");
define("LANG_CHARSET_CODE","utf-8");
define("LANG_CHARSET_MAIL_CONTENT_TYPE","text/html");
define("LANG_CHARSET","text/html; charset=" . LANG_CHARSET_CODE);

// [ Global HTML Strings ]
define("LANG_HTML_TITLE","Cerberus 服务台:: 电子邮件管理// 客户关系管理// 派工单系统");

// Cerberus Global Strings
define("LANG_LOGGING_IN","进入系统 ...");
define("LANG_CERB_ERROR_ACCESS","Cerberus [错误]：您无权进入此页。试图登陆。");
define("LANG_CERB_ERROR_DB_CONNECT","Cerberus [错误]：无法连接数据库。");
define("LANG_CERB_ERROR_TICKET_NAN","CERBERUS: 派工单号必须为数字。");

// Choose a Date Strings
define("LANG_CHOOSEDATE_CHOOSEDATE","Choose a Date:");
define("LANG_CHOOSEDATE_SUN","Sun");
define("LANG_CHOOSEDATE_MON","Mon");
define("LANG_CHOOSEDATE_TUE","Tue");
define("LANG_CHOOSEDATE_WED","Wed");
define("LANG_CHOOSEDATE_THU","Thu");
define("LANG_CHOOSEDATE_FRI","Fri");
define("LANG_CHOOSEDATE_SAT","Sat");

define("LANG_CERB_SUCCESS_CONFIG","成功：配置已更新！");
define("LANG_CERB_WARNING_DEMO","警告：在DEMO模式下，不保存配置的更改！");

// [ Global Word Strings ]

// Capitalized Words
define("LANG_WORD_AGE","年龄");
define("LANG_WORD_ALL","所有");
define("LANG_WORD_BILLABLE","Billable");
define("LANG_WORD_CATEGORY","类别");
define("LANG_WORD_CHARGEABLE","Chargeable");
define("LANG_WORD_CLONE","Clone");
define("LANG_WORD_COMPANY", "Company");
define("LANG_WORD_CREATE","建立");
define("LANG_WORD_CREATED","Created");
define("LANG_WORD_COMMENT","注释");
define("LANG_WORD_DUE", "Due");
define("LANG_WORD_COMMIT","提交");
define("LANG_WORD_DATES","日期");
define("LANG_WORD_DELETE","删除");
define("LANG_WORD_EDIT","编辑");
define("LANG_WORD_ENABLED", "Enabled");
define("LANG_WORD_ERROR","错误");
define("LANG_WORD_EXPIRES","Expires");
define("LANG_WORD_FALSE","False");
define("LANG_WORD_FILTER","过滤");
define("LANG_WORD_FILTERS","过滤"); // might have a problem here, no plurals in Chinese.
define("LANG_WORD_FOR","为了"); // "for" has many meaning in Chinese.  Have to check the entire context.
define("LANG_WORD_FROM","出自");
define("LANG_WORD_HIGHEST","最高");
define("LANG_WORD_HOME","主页");
define("LANG_WORD_HOURS","Hours");
define("LANG_WORD_ID","ID"); // probably best to keep it as ID
define("LANG_WORD_KNOWLEDGEBASE","知识库");
define("LANG_WORD_LANGUAGE","语言");
define("LANG_WORD_LAST","最后");
define("LANG_WORD_LAYOUT","Layout");
define("LANG_WORD_LOGOUT","退出");
define("LANG_WORD_LOWEST","最低");
define("LANG_WORD_MINUTES","分钟");
define("LANG_WORD_NEXT","下一步");
define("LANG_WORD_NOBODY","无人");
define("LANG_WORD_OVERDUE", "Overdue");
define("LANG_WORD_NONE","没有");
define("LANG_WORD_OFF","关");
define("LANG_WORD_ON","开");
define("LANG_WORD_OWNER","所有者");
define("LANG_WORD_PREFERENCES","个人偏好"); // as in: individual settings
define("LANG_WORD_PREV","上一步");
define("LANG_WORD_PRIORITY","优先权");
define("LANG_WORD_PRIVATE","私人的");
define("LANG_WORD_PUBLIC","公众的");
define("LANG_WORD_QUEUE","队列");
define("LANG_WORD_REQUESTER", "Requester");
define("LANG_WORD_QUEUES","队列");
define("LANG_WORD_READ","阅读");
define("LANG_WORD_REPLY","回答");
define("LANG_WORD_RESULTS","结果"); // as in: 1 - 10 of 100 results
define("LANG_WORD_SAVE_CHANGES","Save Changes");
define("LANG_WORD_SEARCH","搜索");
define("LANG_WORD_SPAM", "Spam");
define("LANG_WORD_SPAM_PROBABILITY", "Spam probability");
define("LANG_WORD_SET","设置");
define("LANG_WORD_SETTING","Setting");
define("LANG_WORD_SHOW","显示"); // as in: show 1 - 10 of 1000
define("LANG_WORD_SHOWING","陈列");
define("LANG_WORD_SUBJECT","题目");
define("LANG_WORD_SUCCESS","成功");
define("LANG_WORD_TICKET_VIEW","Ticket View");
define("LANG_WORD_TICKET_USERS", "Ticket Users");
define("LANG_WORD_TRAINING", "Training");
define("LANG_WORD_TRUE", "True");
define("LANG_WORD_STATUS","状态");
define("LANG_WORD_TICKET","派工单");  // as in: Trouble Ticket
define("LANG_WHY_QUESTION", "why?");
define("LANG_WORD_TICKETS","派工单");	// no plural in Chinese Language
define("LANG_WORD_TOTALS","总计");
define("LANG_WORD_WROTE_LAST", "Wrote Last");
define("LANG_WORD_PASSWORD","Password");
define("LANG_WORD_PAYABLE","Payable");
define("LANG_WORD_CONFIRM","Confirm");
define("LANG_WORD_ADD","Add");
define("LANG_WORD_SELECT","Select");
define("LANG_WORD_CONTACT","Contact");
define("LANG_WORD_OLD","old");
define("LANG_WORD_AGO","ago"); // as in: 10 days ago
define("LANG_WORD_WATCHER","观察者");
define("LANG_WORD_WORKED","工作的");
define("LANG_WORD_WRITE","书写");
define("LANG_WORD_TO","to"); // as in: 1 to 10 of 100 matches
define("LANG_WORD_OR", "or");
define("LANG_WORD_QUESTION","Question");
define("LANG_WORD_SEARCH_RESULTS","Search Results");
define("LANG_WORD_TO2","to"); // as in: Message from x to y

define("LANG_WORD_NOTE","Note:"); // as in: "Note: it is important ..."

define("LANG_WORD_SAVE_CHANGES","Save Changes");
define("LANG_WORD_DATE","Date");
define("LANG_WORD_UPDATE","Update");
define("LANG_WORD_VALUE","Value");

define("LANG_WORD_BY","经由"); // as in: Sent 01/01/2000 by John
define("LANG_WORD_OF","共有"); // as in: 1 to 10 of 100 matches

// Abbreviations
define("LANG_WORD_MINUTE_ABBR","分"); // as in: 1 min

// [ Global Buttons ]
define("LANG_BUTTON_GO", "Go!");
define("LANG_BUTTON_SAVE","保存");
define("LANG_BUTTON_SAVE_CHANGES","Save Changes");
define("LANG_BUTTON_SUBMIT","提交");
define("LANG_BUTTON_SUBMIT_CHANGES","提交变动");

// [ Ticket Statuses ]
define("LANG_STATUS_NEW","新的");
define("LANG_STATUS_AWAITING_REPLY","awaiting-reply");
define("LANG_STATUS_BOUNCED","bounced");
define("LANG_STATUS_CUSTOMER_REPLY","customer-reply");
define("LANG_STATUS_RESOLVED","已解决");
define("LANG_STATUS_DEAD","废弃的");

// [ Ticket Priorities ]  - [JSJ]
define("LANG_PRIORITY_UNASSIGNED", "Unassigned");        
define("LANG_PRIORITY_NONE", "None");     
define("LANG_PRIORITY_LOW", "Low");       
define("LANG_PRIORITY_MEDIUM", "Medium");     
define("LANG_PRIORITY_HIGH", "High");   
define("LANG_PRIORITY_CRITICAL", "Critical");        
define("LANG_PRIORITY_EMERGENCY", "Emergency");  

// [ Configuration Strings ]
define("LANG_CONFIG_CLIENT_BROWSER","客户浏览器");
define("LANG_CONFIG_GUI_VERSION"," Cerberus图形界面版");
define("LANG_CONFIG_MACHINE_TYPE","机器型号");
define("LANG_CONFIG_SERVER_SOFTWARE","服务器软件");

define("LANG_CONFIG_ADDRESS_COMMA","逗号");
define("LANG_CONFIG_ADDRESS_DELIMITER","分号:");
define("LANG_CONFIG_ADDRESS_FILE","文件类型:");
define("LANG_CONFIG_ADDRESS_FILE_FILE","文件输出");
define("LANG_CONFIG_ADDRESS_FILE_SCREEN","屏幕打印");
define("LANG_CONFIG_ADDRESS_LINE","换行");
define("LANG_CONFIG_ADDRESS_NOTE","<b>注意:</b> 打印结果将在新窗口中打开。");
define("LANG_CONFIG_ADDRESS_QUEUES","选择队列:");
define("LANG_CONFIG_ADDRESS_TITLE","输出地址");

define("LANG_CONFIG_ADDRESSES_BANNED","禁止");
define("LANG_CONFIG_ADDRESSES_FROM","发件地址");
define("LANG_CONFIG_ADDRESSES_REPLY","回复地址");
define("LANG_CONFIG_ADDRESSES_SEARCH","搜索");
define("LANG_CONFIG_ADDRESSES_SEARCH_EMAIL","搜索一个电子邮件地址:");
define("LANG_CONFIG_ADDRESSES_TITLE","电子邮件地址");

define("LANG_CONFIG_BRANDING_TITLE","标记-定制Cerberus服务台");
define("LANG_CONFIG_BRANDING_LOGO","公司商标:");
define("LANG_CONFIG_BRANDING_RULES","(必须为GIF格式；高度小于100象素，宽度任意。)");
define("LANG_CONFIG_BRANDING_NOTE","如果你的浏览器设置为图象贮藏，新的图标有可能无法立即显示。请重刷新。");
define("LANG_CONFIG_BRANDING_DEFAULT","使用默认值");

define("LANG_CONFIG_CUSTOM_FIELDS","自定义栏位");
define("LANG_CONFIG_MENU_TICKET_FIELDS","派工单栏位");
define("LANG_CONFIG_MENU_REQUESTOR_FIELDS","请求栏位");

define("LANG_CONFIG_FIELDS_TICKET_TITLE","派工单自定义栏位配置");
define("LANG_CONFIG_FIELDS_TICKET_EDIT_EDIT","编辑派工单自定义栏位");
define("LANG_CONFIG_FIELDS_TICKET_EDIT_CREATE","建立派工单自定义栏位");

define("LANG_CONFIG_FIELDS_TITLE","请求自定义栏位配置");
define("LANG_CONFIG_FIELDS_CREATE","建立一个新的自定义栏位");
define("LANG_CONFIG_FIELDS_DEL_CONFIRM","确认要删除这些自定义栏位吗？");
define("LANG_CONFIG_FIELDS_EDIT_EDIT","编辑请求自定义栏位");
define("LANG_CONFIG_FIELDS_EDIT_CREATE","建立请求自定义栏位");
define("LANG_CONFIG_FIELDS_EDIT_NAME","栏位名称");
define("LANG_CONFIG_FIELDS_EDIT_NAME_IE","(例如：操作系统)");
define("LANG_CONFIG_FIELDS_EDIT_TYPE","栏位类型");
define("LANG_CONFIG_FIELDS_EDIT_TYPE_S","单行");
define("LANG_CONFIG_FIELDS_EDIT_TYPE_T","文字框");
define("LANG_CONFIG_FIELDS_EDIT_TYPE_D","下拉框");
define("LANG_CONFIG_FIELDS_EDIT_TYPE_E","Date Field");
define("LANG_CONFIG_FIELDS_EDIT_OPTIONS","栏位选项");
define("LANG_CONFIG_FIELDS_EDIT_OPTIONS_IE","(下拉框唯一使用)");
define("LANG_CONFIG_FIELDS_EDIT_OPTIONS_IE2","(输入引号, 例如: &quot;红&quot;,&quot;绿&quot;,&quot;蓝&quot;)");

define("LANG_CONFIG_FEEDBACK","反馈");
define("LANG_CONFIG_FEEDBACK_TITLE","给予反馈");
define("LANG_CONFIG_FEEDBACK_SUBJECT","题目");
define("LANG_CONFIG_FEEDBACK_NAME","你的姓名");
define("LANG_CONFIG_FEEDBACK_EMAIL","你的电子邮件");
define("LANG_CONFIG_FEEDBACK_SUBMIT","提交反馈");
define("LANG_CONFIG_FEEDBACK_SUCCESS","成功：反馈已经发送！");

define("LANG_CONFIG_KBASECAT_TITLE","知识库种类配置");
define("LANG_CONFIG_KBASECAT_CREATE","建立新的类别");
define("LANG_CONFIG_KBASECAT_CONFIRM","你确定要删除这些知识库种类吗？\\n这会解除知识库解决方案与这些种类的联系。");
define("LANG_CONFIG_KBASECAT_SUBCAT_NODEL","Cerberus 服务台 -错误-\\n含有子目录的种类不能够被删除。\\n请先删除子目录。");
define("LANG_CONFIG_KBASECAT_ADD","添加新的知识库类别");
define("LANG_CONFIG_KBASECAT_EDIT","编辑新的知识库类别");
define("LANG_CONFIG_KBASECAT_CATNAME","类别名");
define("LANG_CONFIG_KBASECAT_EXAMPLE","(例如: &quot;拨号&quot;, &quot;FTP&quot;, &quot;电子邮件&quot;)");
define("LANG_CONFIG_KBASECAT_PARENT","父类别");
define("LANG_CONFIG_KBASECAT_NONE","没有 (上层)");

define("LANG_CONFIG_KEY_TITLE","产品注册码");
define("LANG_CONFIG_KEY_SUBMIT","更新注册码");
define("LANG_CONFIG_KEY_SUCCESS","成功：产品注册码已更新！");
define("LANG_CONFIG_KEY_WARNING","警告：产品注册码无法在DEMO模式下显示。");
define("LANG_CONFIG_PLUGINS","Plugins");
define("LANG_CONFIG_PLUGINS_SETUP","Click a plugin to configure it.");
define("LANG_CONFIG_PLUGINS_MANAGE","Manage Plugins");
define("LANG_CONFIG_PLUGINS_CONFIGURE","Configure Plugin");
define("LANG_CONFIG_KEY_NOKEY","警告：没有注册码存在。Cerberus 服务台不能运行！");

define("LANG_CONFIG_PURGE_TITLE","维护");
define("LANG_CONFIG_PURGE_DEAD","废弃派工单清除");
# the line below has some problem
define("LANG_CONFIG_PURGE_NOTE"," (删除所有超过24小时标记为废弃的派工单。) ");
define("LANG_CONFIG_PURGE_SUBMIT","执行");
define("LANG_CONFIG_PURGE_SUCCESS","废弃派工单已清除");
define("LANG_CONFIG_PURGE_TEMPDIR","Purge Temp Files");


define("LANG_CONFIG_QUEUE_TITLE","队列配置");
define("LANG_CONFIG_QUEUE_WARNING","你确定删除队列幺？\\n这将删除所有与队列有关的派工单。");
define("LANG_CONFIG_QUEUE_CREATE","建立新队列");

define("LANG_CONFIG_QUEUE_ADD","添加新队列");
define("LANG_CONFIG_QUEUE_EDIT","编辑队列配置");
define("LANG_CONFIG_QUEUE_EDIT_NAME","队列名");
define("LANG_CONFIG_QUEUE_EDIT_NAME_IE","(例如：&quot;支持&quot;)");
define("LANG_CONFIG_QUEUE_EDIT_ADDRESS","队列地址");
define("LANG_CONFIG_QUEUE_EDIT_ADDRESS_IE","(例如：support@yourcompany.com)");
define("LANG_CONFIG_QUEUE_EDIT_PREFIX","队列前缀");
define("LANG_CONFIG_QUEUE_EDIT_PREFIX_IE","(在回复电子邮件里,这将会添加在派工单号前。例如. [<i>前缀</i> #123 ]: )");
define("LANG_CONFIG_QUEUE_EDIT_OPEN","开启自动发送");
define("LANG_CONFIG_QUEUE_EDIT_OPEN_IE","钩选这个选项将会并发送自动回复派工单");
define("LANG_CONFIG_QUEUE_EDIT_CLOSE","自动发送关闭");
define("LANG_CONFIG_QUEUE_EDIT_CLOSE_IE","钩选这个选项将会禁止自动回复派工单");
define("LANG_CONFIG_QUEUE_EDIT_NEW","新的自动回复派工单");
define("LANG_CONFIG_QUEUE_EDIT_NEW_IE","(在信息内, 使用 ##派工单号## 来指定派工单号。)");
define("LANG_CONFIG_QUEUE_EDIT_CLOSED","派工 已解决/关闭 自动回复");
define("LANG_CONFIG_QUEUE_EDIT_CLOSED_IE","(在信息内, 使用 ##派工单号## 来指定派工单号。)");
define("LANG_CONFIG_QUEUE_EDIT_NOID","Cerberus [错误]: 队列ID不存在。任务中止。");

define("LANG_CONFIG_BUG_TITLE","故障报告");
define("LANG_CONFIG_BUG_SUMMARY","故障总结");
define("LANG_CONFIG_BUG_NAME","你的姓名");
define("LANG_CONFIG_BUG_EMAIL","你的电子邮件");
define("LANG_CONFIG_BUG_DESC","详细描述");
define("LANG_CONFIG_BUG_SUBMIT","提交故障");
define("LANG_CONFIG_BUG_SUCCESS","成功：故障报告已发送！");

define("LANG_CONFIG_GROUPS_TITLE","使用者组配置");
define("LANG_CONFIG_GROUPS_CREATE","建立新组");
define("LANG_CONFIG_GROUPS_EDIT_CREATE","建立使用者组");
define("LANG_CONFIG_GROUPS_EDIT_EDIT","编辑使用者组");
define("LANG_CONFIG_GROUPS_EDIT_GROUP","组名");
define("LANG_CONFIG_GROUPS_EDIT_PRIV","特权");
define("LANG_CONFIG_GROUPS_EDIT_OWNER","更改所有者");
define("LANG_CONFIG_GROUPS_EDIT_PRIOR","更改优先权");
define("LANG_CONFIG_GROUPS_EDIT_QUEUE","更改队列");
define("LANG_CONFIG_GROUPS_EDIT_STATUS","更改状态");
define("LANG_CONFIG_GROUPS_EDIT_SUBJECT","更改主题");
define("LANG_CONFIG_GROUPS_EDIT_TAKE","可以 &quot;承担&quot; 未分配的派工单");
define("LANG_CONFIG_GROUPS_EDIT_REQ","从这组内隐藏派工单申请者的电子邮件地址");
define("LANG_CONFIG_GROUPS_EDIT_KB","知识库");
define("LANG_CONFIG_GROUPS_EDIT_KB_VIEW","浏览知识库");
define("LANG_CONFIG_GROUPS_EDIT_KB_SEARCH","搜索知识库");
define("LANG_CONFIG_GROUPS_EDIT_ARTICLE_CREATE","建立文章");
define("LANG_CONFIG_GROUPS_EDIT_ARTICLE_EDIT","编辑文章");
define("LANG_CONFIG_GROUPS_EDIT_ARTICLE_DEL","删除文章");
define("LANG_CONFIG_GROUPS_EDIT_PREF","可以改变自己帐号的个人偏好");
define("LANG_CONFIG_GROUPS_EDIT_CONFIG","配置");
define("LANG_CONFIG_GROUPS_EDIT_CONFIG_MENU","可以进入配置菜单");
define("LANG_CONFIG_GROUPS_EDIT_USER_CREATE","建立使用者");
define("LANG_CONFIG_GROUPS_EDIT_USER_EDIT","编辑使用者");
define("LANG_CONFIG_GROUPS_EDIT_USER_DEL","删除使用者");
define("LANG_CONFIG_GROUPS_EDIT_QUEUE_CREATE","建立队列");
define("LANG_CONFIG_GROUPS_EDIT_QUEUE_EDIT","编辑队列");
define("LANG_CONFIG_GROUPS_EDIT_QUEUE_DELETE","删除队列");
define("LANG_CONFIG_GROUPS_EDIT_KB_CREATE","建立知识库种类");
define("LANG_CONFIG_GROUPS_EDIT_KB_EDIT","编辑知识库种类");
define("LANG_CONFIG_GROUPS_EDIT_KB_DEL","删除知识库种类");
define("LANG_CONFIG_GROUPS_EDIT_LOGO","图标上载");
define("LANG_CONFIG_GROUPS_EDIT_PURGE","维护清除废弃派工单");
define("LANG_CONFIG_GROUPS_EDIT_BLOCK","阻止电子邮件发件人");
define("LANG_CONFIG_GROUPS_EDIT_EMAIL","下载电子邮件地址");
define("LANG_CONFIG_GROUPS_EDIT_KEY","上传注册码选项");
define("LANG_CONFIG_GROUPS_EDIT_BUG","故障报告选项");
define("LANG_CONFIG_GROUPS_EDIT_FEEDBACK","反馈选项");
define("LANG_CONFIG_GROUPS_DEL_CONFIRM","你确定要删除这些组吗？");
define("LANG_CONFIG_GROUPS_NOID","Cerberus [错误]: 该组ID不存在。任务中止。");
define("LANG_CONFIG_GROUPS_SUCCESS","成功: 使用者组已更新！");

define("LANG_CONFIG_MENU_USERS","使用者");
define("LANG_CONFIG_MENU_USERS_NEW","建立新使用者");
define("LANG_CONFIG_MENU_USERS_EDIT","编辑/删除使用者");
define("LANG_CONFIG_MENU_USERS_GROUPS","管理组");
define("LANG_CONFIG_MENU_QUEUE_NEW","建立新队列");
define("LANG_CONFIG_MENU_QUEUE_EDIT","编辑/删除队列");
define("LANG_CONFIG_MENU_KBCAT_NEW","新类别");
define("LANG_CONFIG_MENU_KBCAT_EDIT","浏览/列出类别");
define("LANG_CONFIG_MENU_BRANDING","标记");
define("LANG_CONFIG_MENU_BRANDING_UPLOAD","上载图标");
define("LANG_CONFIG_MENU_MAINT_PURGE","清除废弃派工单");
define("LANG_CONFIG_MENU_EMAIL","电子邮件地址");
define("LANG_CONFIG_MENU_EMAIL_BLOCK","阻止发件人");
define("LANG_CONFIG_MENU_EMAIL_EXPORT","输出地址");
define("LANG_CONFIG_MENU_PRODUCT","产品选项");
define("LANG_CONFIG_MENU_PRODUCT_KEY","上传产品注册码");
define("LANG_CONFIG_MENU_BUG","故障报告");
define("LANG_CONFIG_MENU_FEEDBACK","给予反馈");
define("LANG_CONFIG_MENU_NOTE","请从左边的菜单选择配置选项。");

define("LANG_CONFIG_USER_TITLE","使用者配置");
define("LANG_CONFIG_USER_CREATE","建立新的使用者");
define("LANG_CONFIG_USER_CONFIRM","你确定要删除这些使用者吗？");

define("LANG_CONFIG_USER_EDIT_NEW","新使用者");
define("LANG_CONFIG_USER_EDIT_EDIT","编辑使用者");
define("LANG_CONFIG_USER_EDIT_NAME","使用者姓名");
define("LANG_CONFIG_USER_EDIT_NAME_IE","(例如: &quot;吴约翰 &quot;)"); // John Wu the director
define("LANG_CONFIG_USER_EDIT_EMAIL","使用者电子邮件地址");
define("LANG_CONFIG_USER_EDIT_EMAIL_IE","(例如: support@yourcompany.com)");
define("LANG_CONFIG_USER_EDIT_LOGIN","使用者登陆名");
define("LANG_CONFIG_USER_EDIT_LOGIN_IE","(使用者登入系统所使用的登陆名)");
define("LANG_CONFIG_USER_EDIT_PASS","使用者密");
define("LANG_CONFIG_USER_EDIT_PASS_VER","使用者密码（再次确认）");
define("LANG_CONFIG_USER_EDIT_PASS_VER_IE","(再次输入密码以便确认)");
define("LANG_CONFIG_USER_EDIT_GROUP","使用者组");
define("LANG_CONFIG_USER_EDIT_NONE","没有（关闭）");
define("LANG_CONFIG_USER_EDIT_GROUP_IE","(超级使用者不需要)");
define("LANG_CONFIG_USER_EDIT_SUPERUSER","使用者为超级使用者");
define("LANG_CONFIG_USER_EDIT_SUPERUSER_IE","(钩选这个选项将会允许这个使用者拥有超级使用者的权限)");
define("LANG_CONFIG_USER_EDIT_QUEUES","队列访问");
define("LANG_CONFIG_USER_EDIT_LASTLOGIN","使用者上一次登陆");
define("LANG_CONFIG_USER_EDIT_NOSPACES","对不起,空间不准许。");
define("LANG_CONFIG_USER_EDIT_PWTWICE","请输入您的密码两次。");
define("LANG_CONFIG_USER_EDIT_PWTWICE_ERROR","你没有输入同样的新密码两次。请重新输入。");
define("LANG_CONFIG_USER_EDIT_NOID","Cerberus [错误]: 使用者ID不存在。任务中止。");
define("LANG_CONFIG_USER_EDIT_SUCCESS","成功：使用者已更新！");

// Create Ticket Strings
define("LANG_CREATE_IN","新派工单在");
define("LANG_CREATE_TICKET","建立派工单");
define("LANG_CREATE_COMMA_DELIMITED","(comma-delimited)");
define("LANG_CREATE_SPELLCHECK","Spellcheck");
define("LANG_CREATE_SENDER_INSTRUCTIONS","(we're creating a ticket for this e-mail address; they will receive a copy of all future correspondence regarding the ticket)");
define("LANG_CREATE_OPTIONS_NOCOPYTOREQUESTER","Don't send a copy of the ticket to the requester.");
define("LANG_CREATE_ATTACHMENTS_NO","No Attachments");
define("LANG_CREATE_ATTACHMENTS_ADD","Add/Remove Attachments");

// File Upload Strings
define("LANG_FILE_UPLOAD_WINDOW_TITLE","Upload Attachment");
define("LANG_FILE_UPLOAD_INSTRUCTIONS","You may attach multiple files to this message by using Browse and Attach 
below.  To remove a file, highlight it and click Remove.  Click Done to return to your message.");
define("LANG_FILE_UPLOAD_ATTACH","Attach =>");
define("LANG_FILE_UPLOAD_REMOVE","<= Remove");
define("LANG_FILE_UPLOAD_DONE", "Done");
define("LANG_FILE_UPLOAD_TOTAL_SIZE", "Total size");
define("LANG_FILE_UPLOAD_INPROGRESS", "Upload in progress");
define("LANG_FILE_UPLOAD_INSTRUCTIONS","You may attach multiple files to this message by using Browse and Attach below. To remove a file, highlight it and click Remove. Click Done to return to your message.");
define("LANG_CREATE_DESCRIBE","在下描述事件");
define("LANG_CREATE_SUBMIT","建立派工单");

// Login Strings
define("LANG_LOGIN_PW_RESET","密码已重新设置");
define("LANG_LOGIN_PW_CODE","为了完成密码更改申请，请确认编码并输入到同一页。（第二步）\\n编码");
define("LANG_LOGIN_PW_NEW","跟随你的要求,你的密码已重设置。\\n现已设置为");
define("LANG_LOGIN_LOGIN","登陆");
define("LANG_LOGIN_PW","密码");
define("LANG_LOGIN_SUBMIT","登陆");
define("LANG_LOGIN_PW_SENT","发送：新的密码已经通过电子邮件发送给您了！");
define("LANG_LOGIN_PW_VERIFY","发送：密码更改的确认码已经通过电子邮件发送给您了！");
define("LANG_LOGIN_PW_LOST1","忘记密码？（第一步）");
define("LANG_LOGIN_PW_LOST1_IE","(电子邮件地址)");
define("LANG_LOGIN_PW_LOST2","电子邮件重新设置确认码。（第二步）");
define("LANG_LOGIN_PW_LOST2_IE","(发送到电子邮件的编码)");
define("LANG_LOGIN_PW_LOST_SEND","发送");
define("LANG_LOGIN_FAILED","失败：登陆名/密码匹配错误！");

// Header Strings
define("LANG_HEADER_RESULTS","搜索结果");
define("LANG_HEADER_KB","知识库");
define("LANG_HEADER_REPORTS","reports");
define("LANG_HEADER_CONFIG","配置");
define("LANG_HEADER_HELP","帮助");
define("LANG_HEADER_UNREAD_MESSAGES", "unread message(s)");
define("LANG_HEADER_DASHBOARD", "my cerberus (dashboard)");
define("LANG_HEADER_NOTIFICATION", "my notification");
define("LANG_HEADER_PROJECTS", "my projects / tasks");
define("LANG_HEADER_MESSAGES", "my messages");
define("LANG_HEADER_QUICKASSIGN", "my quick assign / watcher");
define("LANG_HEADER_PREFERENCES", "my preferences");
define("LANG_HEADER_JUMPTO", "Jump to");
define("LANG_HEADER_QUICKQUEUE", "Quick Queue");
define("LANG_HEADER_CONFIGURE_QUICKASSIGN", "Configure my quick assign settings");
define("LANG_HEADER_CONTACTS", "contacts");
define("LANG_HEADER_LAST_VIEWED", "Last Viewed");
define("LANG_HEADER_NEW_TICKET","新派工单在");
define("LANG_HEADER_GOTO_TICKET","到派工单");
define("LANG_FOOTER_WHOS_ONLINE", "Who's Online");
define("LANG_FOOTER_SEND_PM", "send pm");
define("LANG_HEADER_ADVANCED", "Advanced Options");
define("LANG_HEADER_ADVANCED_ON", "Advanced Options On");
define("LANG_HEADER_ADVANCED_OFF", "Advanced Options Off");
define("LANG_HEADER_SAVE_PAGE_LAYOUT", "Save Page Layout");


// Footer Strings
define("LANG_FOOTER_TM","&quot;Cerberus 服务台&quot; 是WebGroup Media LLC&#153; 的注册商标");
define("LANG_FOOTER_POWERED","powered by");

// Audit Log Action Strings
// NOTE: leave the %s (percent 's') _AS IS_, do not translate
define("LANG_AUDIT_LOG_TITLE","派工单审查日志（最后5次）");
define("LANG_AUDIT_LOG_TITLE_LATEST_5","Ticket Audit Log (Latest 5 Actions)");
define("LANG_AUDIT_ACTION_OPENED","派工单已建立");
define("LANG_AUDIT_ACTION_CHANGED_ASSIGN","<b>%s</b> 分配给 <b>%s</b>。");
define("LANG_AUDIT_ACTION_CHANGED_STATUS","<b>%s</b> 更改状态到 <b>%s</b>。");
define("LANG_AUDIT_ACTION_REPLIED","<b>%s</b> 答复申请者。");
define("LANG_AUDIT_ACTION_COMMENTED","<b>%s</b> 注释了派工单。");
define("LANG_AUDIT_ACTION_CHANGED_QUEUE","<b>%s</b> 移动到队列到 <b>%s</b>。");
define("LANG_AUDIT_ACTION_CHANGED_PRIORITY","<b>%s</b> 更改优先权到 <b>%s</b>。");
define("LANG_AUDIT_ACTION_REQUESTOR_RESPONSE","<b>申请者</b> 已回答工单。");
define("LANG_AUDIT_ACTION_TICKET_REOPENED","申请者重新开始派工单。");
define("LANG_AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR","<b>%s</b>更改申请者自定义栏位。");
define("LANG_AUDIT_ACTION_CUSTOM_FIELDS_TICKET","<b>%s</b>更改派工单自定义栏位");
define("LANG_AUDIT_ACTION_TICKET_CLONED_FROM","<b>%s</b> cloned ticket from #%s."); // needs translation
define("LANG_AUDIT_ACTION_TICKET_CLONED_TO","<b>%s</b> cloned ticket to #%s."); // needs translation
define("LANG_AUDIT_ACTION_RULE_CHOWNER","<b>Parser rule</b> assigned ticket to %s."); // needs translation
define("LANG_AUDIT_ACTION_RULE_CHSTATUS","<b>Parser rule</b> changed status to %s."); // needs translation
define("LANG_AUDIT_ACTION_RULE_CHQUEUE","<b>Parser rule</b> changed queue to %s."); // needs translation
define("LANG_AUDIT_ACTION_RULE_CHPRIORITY","<b>Parser</b> mail rule changed priority to <b>%s</b>.");
define("LANG_AUDIT_ACTION_THREAD_FORWARD","<b>%s</b> forwarded a message to <b>%s</b>."); // needs translation
define("LANG_AUDIT_ACTION_ADD_REQUESTER","<b>%s</b> added <b>%s</b> to the ticket requesters."); // needs translation
define("LANG_AUDIT_ACTION_MERGE_TICKET","<b>%s</b> merged ticket <b>%s</b> into this ticket."); // needs translation
define("LANG_AUDIT_ACTION_THREAD_BOUNCE","<b>%s</b> bounced an e-mail to <b>%s</b>.");	// [JXD]: Bounce feature

// Display Ticket Strings
define("LANG_DISPLAY_GLANCE","看一下派工单");
define("LANG_DISPLAY_UPDATE","更新派工单");
define("LANG_DISPLAY_CREATED","建立");
define("LANG_DISPLAY_LAST_CONTACT","最后联系");
define("LANG_DISPLAY_DUE","到期");
define("LANG_DISPLAY_UPDATED","已更新");
define("LANG_DISPLAY_CUST_HISTORY","客户服务历史");
define("LANG_DISPLAY_THREAD","派工单线程");
define("LANG_DISPLAY_THREAD_ADD_TIME","add time tracking entry");
define("LANG_DISPLAY_THREAD_TOGGLE_ACTIVITY","toggle activity threads");
define("LANG_DISPLAY_THREAD_TOGGLE_TIME","toggle time tracking threads");
define("LANG_DISPLAY_TIME_TRACKING_TITLE","Time Tracking Entry");
define("LANG_DISPLAY_TIME_TRACKING_WORK_DATE","Work Date");
define("LANG_DISPLAY_TIME_TRACKING_DATE_BILLED","Date Billed");
define("LANG_DISPLAY_TIME_TRACKING_WORK_AGENT","Work Agent");
define("LANG_DISPLAY_TIME_TRACKING_WORK_SUMMARY","Work Summary");
define("LANG_DISPLAY_TIME_TRACKING_CONFIRM_DEL","(type <b>YES</b> to confirm deletion)");
define("LANG_DISPLAY_REQUESTOR","申请者");
define("LANG_DISPLAY_TIME_WORKED","工作时间");
define("LANG_DISPLAY_SOURCE_HIDE","隐藏资源");
define("LANG_DISPLAY_SOURCE_VIEW","浏览资源");
define("LANG_DISPLAY_ATTACHMENTS","附件");
define("LANG_DISPLAY_DISPLAY","显示线程");
define("LANG_DISPLAY_EDIT_INFO","编辑申请者信息");
define("LANG_DISPLAY_CUST_INFO","申请者信息");
define("LANG_DISPLAY_BATCH","批");
define("LANG_DISPLAY_ESCALATION","增加");
define("LANG_DISPLAY_LOG","日志");
define("LANG_DISPLAY_EDIT_FIELDS","编辑申请者自定义栏位给");
define("LANG_DISPLAY_SLA_TITLE", "Company/Service Level Agreement Details");
define("LANG_DISPLAY_SLA_COMPANY", "Company Name");
define("LANG_DISPLAY_SLA_COMPANY_UNASSIGN", "unassign from company");
define("LANG_DISPLAY_SLA_COMPANY_ADD", "Add a Company");
define("LANG_DISPLAY_SLA_COMPANY_NUMBER_ADRESSES", "# of Addresses");
define("LANG_DISPLAY_SLA_COMPANY_NUMBER_TICKETS", "# of Tickets");
define("LANG_DISPLAY_SLA_CONTRACT", "Service Contract");
define("LANG_DISPLAY_SLA_ACTION_ASSIGN_REQUESTER_TO_COMPANY", "Assign requester to company");

// Display Ticket Properties Strings
define("LANG_DISPLAY_SHOW_CALENDAR", "show calendar");
define("LANG_DISPLAY_EDIT_SENDER", "edit");
define("LANG_DISPLAY_DUE_INSTRUCTIONS", "Use calendar or enter <b><i>mm/dd/yy</i></b>");
define("LANG_DISPLAY_COMPANYCONTACT","Company / Contact");
define("LANG_DISPLAY_NO_COMPANY","none");
define("LANG_DISPLAY_NO_SLA_PLAN","none");
define("LANG_DISPLAY_SLA_COVERAGE","SLA Coverage:");
define("LANG_DISPLAY_SHOW_COMPANY_DETAILS", "view company details");
define("LANG_DISPLAY_SHOW_CONTACT_DETAILS", "view contact details");
define("LANG_DISPLAY_ADDRESS_NOT_ASSIGNED", "This address isn't assigned to a contact or company.");
define("LANG_DISPLAY_ANTISPAM", "Anti-Spam");
define("LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_SEARCH", "view / search contacts");
define("LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_CREATE", "create a new contact for");

define("LANG_DISPLAY_SLABOX_TABLE_QUEUE","Queue");
define("LANG_DISPLAY_SLABOX_TABLE_QUEUEMODE","Queue Mode");
define("LANG_DISPLAY_SLABOX_TABLE_SLASCHEDULE","SLA Schedule");
define("LANG_DISPLAY_SLABOX_TABLE_TARGETRESPONSETIME","Target Response Time");

define("LANG_DISPLAY_ANTISPAM_HEADING", "Cerberus Anti-Spam Measures");
define("LANG_DISPLAY_ANTISPAM_NOT_TRAINED", "Ticket Not Trained");
define("LANG_DISPLAY_ANTISPAM_TEXT", "Cerberus implements Bayesian filtering to give a probability rating on whether or not a given ticket is spam. A group of \"interesting\" words are sampled based on their deviation from uninteresting words found in your e-mail, by being either extremely innocent (e.g., your company name) or extremely spammy (e.g., viagra). These words are then ranked by Cerberus's interest in them, 0 (lowest) to .4999 (highest), and an overall probability is generated. Initially the tokens chosen may seem somewhat random -- this is perfectly normal for increasing the experience base of the filter.<br><br>An important part of combating spam with Bayesian filtering is training the filter with both good and spammy e-mail. In the ticket display screen you have the option of training the filter to recognize more tokens of each type of e-mail. The filter is adaptive -- if spammers change their tactics through wording, intentional mispelling, changing URLs, etc. the filter training will help combat it.<br><br>After you've trained your filter for a while, you can use a high spam probability (0.90 = 90%) in a new ticket mail rule for various behavior, such as: sorting likely spam into a 'quarantine' queue for later review, automatically deleting spam, etc.<br><br>For more information on Bayesian spam filtering, <a href=\"http://www.paulgraham.com/antispam.html\" class=\"cer_maintable_heading\" target=\"_blank\">read through</a> Paul Graham's excellent articles on the subject.");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_HEADER", "Ranked \"Interesting\" Words That Factored Into This Probability Rating");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_TOKEN", "Token");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_IN_SPAM", "In Spam");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_IN_NON_SPAM", "In Non-Spam");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_PROBABILITY", "Probability");
define("LANG_DISPLAY_ANTISPAM_INTERESTING_WORDS_INTEREST_FACTOR", "Interest Factor");
							
define("LANG_DISPLAY_PROPS","属性");
define("LANG_DISPLAY_PROPS_TAKE","承担派工单");
define("LANG_DISPLAY_PROPS_NOTAKE","错误：派工单已存在");
define("LANG_DISPLAY_PROPS_UPDATED","成功：派工单属性已更新！");
define("LANG_DISPLAY_PROPS_TICKET_SUBJECT","派工单主题");
define("LANG_DISPLAY_PROPS_TICKET_STATUS","派工单状态");
define("LANG_DISPLAY_PROPS_TICKET_OWNER","派工单所有者");
define("LANG_DISPLAY_PROPS_TICKET_QUEUE","派工单队列");
define("LANG_DISPLAY_PROPS_TICKET_PRIORITY","优先权");
define("LANG_DISPLAY_PROPS_SUBMIT","更新派工单");
define("LANG_DISPLAY_VITAL_SIGNS", "Ticket Vital Signs");
define("LANG_DISPLAY_NUMBER_REQUESTERS", "# of Requesters");

define("LANG_DISPLAY_JUMP_TO_LATEST","jump to latest message");
define("LANG_DISPLAY_CUSTOMIZE_LAYOUT","customize page layout");
define("LANG_DISPLAY_BACK_TO_TOP","back to top");

// Header Strings
define("LANG_HEADER_LOGGED","登录为");

// Refresh Box
define("LANG_REFRESH_TITLE","每次自动刷新用户界面");
define("LANG_REFRESH_DONT","不自动刷新");

// Assigned / Unassigned List Strings
define("LANG_UNASSIGNED_TITLE","所有队列里最新的未分配派工单");
define("LANG_FILTER_RESP_WITHOUT","只显示未答复的派工单");
define("LANG_FNR_TITLE","Cerberus Fetch &amp; Retrieve&trade; - Suggested Solutions");
define("LANG_FNR_HELPFUL","Mark Suggestion as Helpful");
define("LANG_FNR_NO_ARTICLES","Sorry, no helpful articles found.");
define("LANG_FNR_NO_ARTICLES_2","Try suggesting an article manually.");
define("LANG_FNR_NOT_HELPFUL","Mark Suggestion as Not Helpful");
define("LANG_FNR_SUGGEST_ARTICLE","suggest an article with a better solution");
define("LANG_ASSIGNED_TITLE","最高优先派工单分配给");

// Knowledgebase
define("LANG_KB_ARTICLE_IN","知识库文章在");
define("LANG_KB_ARTICLE_CREATE","建立新知识库文章在");
define("LANG_KB_ARTICLE_NO_ARTICLES","知识库文章没有在此类");
define("LANG_KB_ASK_QUESTION", "Ask a Question:");
define("LANG_KB_ARTICLE_ERROR_SUMMARY","摘要栏位已空白。");
define("LANG_KB_ARTICLE_ERROR_DESC","描述栏位已空白。");
define("LANG_KB_ARTICLE_ERROR_SOLUTION","解决方案栏位已空白。");
define("LANG_KB_ARTICLE_DEL_CONFIRM","你确定要删除这篇文章吗？");
define("LANG_KB_SUMMARY","摘要");
define("LANG_KB_ENTRY_DATE","登陆日期");
define("LANG_KB_ENTRY_USER","登陆人");
define("LANG_KB_KEYWORDS","关键词");
define("LANG_KB_KEYWORDS_IE","(空位-限定)");
define("LANG_KB_ARTICLE_USE","文章类型");
define("LANG_KB_DESC_PROB","问题描述");
define("LANG_KB_DESC_SOLUTION","解决方案说明");
define("LANG_KB_ARTICLE_ID","文章ID ");
define("LANG_KB_VIEW_TITLE","文章摘要");
define("LANG_KB_EDIT","编辑此文章");
define("LANG_KB_DELETE","删除此文章");
define("LANG_KB_SYMPTOMS","故障现象");
define("LANG_KB_RESOLUTION","决议");
define("LANG_KB_RETURN","返回种类");
define("LANG_KB_BROWSE","按种类浏览知识库");
define("LANG_KB_BACK","返回顶部");
define("LANG_KB_UP","上一层次");
define("LANG_KB_TOP","顶部");
define("LANG_KB_NO_MATCH","没找到匹配值。");
define("LANG_KB_NO_CATID","Cerberus [错误]：知识库种类ID不存在。任务中止。");
define("LANG_KB_INCLUDE_INFORMATION","(include as much information as possible using your natural writing style)");

// Knowledgebase Search
define("LANG_KB_SEARCH_TITLE","知识库搜索");
define("LANG_KB_SEARCH_ID","Article ID");
define("LANG_KB_SEARCH_TOPIC","主题");
define("LANG_KB_ALL_BRANCHES","所有种类分支");
define("LANG_KB_GOTO_ID","Go to Article ID");
define("LANG_KB_CATEGORIES","Categories");
define("LANG_KB_NO_SUB_CATEGORIES","No sub-categories");
define("LANG_KB_NO_RESULTS","No matching articles");
define("LANG_KB_RESULTS","Suggested Solutions:");
define("LANG_KB_SEARCH_KEYWORD_HEADING","Didn't Find the Answer you were Looking for?");
define("LANG_KB_SEARCH_KEYWORD_INTRO","Try a Keyword Search");
define("LANG_KB_SEARCH_KEYWORD_INSTRUCTIONS","(Enter keywords separated by a space. For example: <i>product warranty information</i>)<br><br>Some high probablility keywords from your question may have already been added for you.");
define("LANG_KB_NO_TRUEID","Cerberus [错误]: 知识库真实的 ID 没有传达.");

// Preferences Strings
define("LANG_PREF_NOMATCH","对不起，使用者ID与密码不匹配。");
define("LANG_PREF_TITLE","使用者个人偏好");
define("LANG_PREF_ASSIGNED_WITHOUT","只显示<i>分配</i>而未答复的派工单");
define("LANG_PREF_UNASSIGNED_WITHOUT","只显示<i>未分配</i>而未答复的派工单");
define("LANG_PREF_SHOW_NUM","显示分配结果数目");
define("LANG_PREF_UNASSIGNED_SHOW_NUM","显示未分配结果数目");
define("LANG_PREF_MSG_TITLE","浏览派工单线程以");
define("LANG_PREF_MSG_OLDEST","按消息日期升序");
define("LANG_PREF_MSG_NEWEST","按消息日期降序");
define("LANG_PREF_PW_CHANGE","修改密码");
define("LANG_PREF_PW_NEW","新密码");
define("LANG_PREF_PW_CURRENT","确认密码");
define("LANG_PREF_PW_VERIFY","校验密码");
define("LANG_PREF_PW_NOTE","注意：修改密码需要重新登陆。");
define("LANG_PREF_AUTO_SIG","自动消息签名");
define("LANG_PREF_AUTO_SIG_PLACEMENT", "Auto Signature Placement:");
define("LANG_PREF_AUTO_SIG_AFTER_QUOTE", "After Quoted Text");
define("LANG_PREF_AUTO_SIG_BEFORE_QUOTE", "Before Quoted Text");
define("LANG_PREF_AUTO_SIG_AUTO_INSERT", "Auto Insert Signature:");
define("LANG_PREF_KEYBOARD_SHORTCUTS", "Keyboard Shortcuts");
define("LANG_PREF_TIMEZONE", "Timezone");


// Contacts Strings
define("LANG_CONTACTS_HEADER","Contacts");
define("LANG_CONTACTS_INSTRUCTIONS","This is a list of companies, contacts and registered contacts with whom you've communicated.");
define("LANG_CONTACTS_SEARCH_CAPTION","Contact Substring Search");
define("LANG_CONTACTS_SEARCH_INSTRUCTIONS","(The substring search will match partial company names, contact names and e-mail addresses. It is not case-sensitive. For example: <i><B>inc</B></i> will match <i><B>Widgets, Inc.</B></i> and <i><B>Acme, Inc.</B></i>, etc.)");
define("LANG_CONTACTS_ADD_COMPANY","add company");
define("LANG_CONTACTS_ADD_REGISTRED","add registered contact");
define("LANG_CONTACTS_HEADER_COMPANIES","Companies &amp; Organizations");
define("LANG_CONTACTS_REGISTRED_VIEW","Registered Contact (Public User Account)");
define("LANG_CONTACTS_REGISTRED_ADD","Add Registered Contact (Public User Account)");
define("LANG_CONTACTS_COMPANY_EDIT_HEADER","Company/Organization Details");
define("LANG_CONTACTS_CONTACT_EDIT_HEADER","Contact Details");
define("LANG_CONTACTS_PHONE","Phone");
define("LANG_CONTACTS_COMPANY_NAME","Company Name");
define("LANG_CONTACTS_CONTACT_NAME","Contact Name");
define("LANG_CONTACTS_MAIL","E-mail Address");
define("LANG_CONTACTS_COMPANY_NUMBER","# of Registered Contacts");
define("LANG_CONTACTS_REGISTRED_NUMBER","Registered Addresses");
define("LANG_CONTACTS_ACCOUNT_NUM","Account Number");
define("LANG_CONTACTS_STREET","Street Address");
define("LANG_CONTACTS_CITY","City");
define("LANG_CONTACTS_STATE","State/Province");
define("LANG_CONTACTS_ZIP","Zip/Postal Code");
define("LANG_CONTACTS_COUNTRY","Country");
define("LANG_CONTACTS_PHONE_WORK","Work Phone");
define("LANG_CONTACTS_PHONE_MOBILE","Mobile Phone");
define("LANG_CONTACTS_PHONE_HOME","Home Phone");
define("LANG_CONTACTS_FAX","Fax");
define("LANG_CONTACTS_MAIL_SHORT","E-mail");
define("LANG_CONTACTS_WEBSITE","Website");
define("LANG_CONTACTS_REGISTRED_RESET_PW","Reset Password");
define("LANG_CONTACTS_REGISTRED_SET_PW","Set Password");
define("LANG_CONTACTS_REGISTRED_PW_NOTE","(leave blank to keep current password)");
define("LANG_CONTACTS_REGISTRED_MAILADR_HEADER","Registered E-mail Addresses for this Contact");
define("LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED","With selected:");
define("LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_NOTHING"," - do nothing - ");
define("LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_UNASSIGN","Unassign from Contact");
define("LANG_CONTACTS_REGISTRED_MAILADR_WITHSELECTED_UPDATE","Update");
define("LANG_CONTACTS_REGISTRED_COMPANY_HEADER","Company Information");
define("LANG_CONTACTS_REGISTRED_COMPANY_SLAPLAN","SLA Plan");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS","A Registered Contact is a user who has access to any public customer services (Cerberus Support Center, etc.) that you may be providing to customers through your various websites. You can group all correspondence from this contact by adding their various e-mail addresses to their record. By adding the contact to a company, you can tie all correspondence to a company record -- and the contact may further benefit from company-wide Service Level Agreements (SLAs), etc.");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS_NOCOMPANY","To assign this contact to a company, enter/paste an<br><b>e-mail address</b> from this contact into the <b><i>Add Contact</i></b><br>field on a Company record.");
define("LANG_CONTACTS_REGISTRED_MAILASSIGN_HEADER","Assign New Contact E-mail Address");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS_NOCOMPANY","To assign this contact to a company, enter/paste an<br><b>e-mail address</b> from this contact into the <b><i>Add Contact</i></b><br>field on a Company record.");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS_VIEW","To delete a registered contact, unassign all e-mail addresses from them.");
define("LANG_CONTACTS_REGISTRED_MAILASSIGN_INSTRUCTIONS","To assign a new e-mail address to this contact, enter/paste<br>the <b><i>e-mail address</i></b> into the box below:<br><b>E-mail:</b>");
define("LANG_CONTACTS_REGISTRED_MAILASSIGN_SUBMIT","Assign");
define("LANG_CONTACTS_COMPANY_SLABOX_HEADERIFEMPTY","Service Level Agreement");
define("LANG_CONTACTS_COMPANY_SLABOX_TABLE_QUEUE","Queue");
define("LANG_CONTACTS_COMPANY_SLABOX_TABLE_QUEUEMODE","Queue Mode");
define("LANG_CONTACTS_COMPANY_SLABOX_TABLE_SLASCHEDULE","SLA Schedule");
define("LANG_CONTACTS_COMPANY_SLABOX_TABLE_TARGETRESPONSETIME","Target Response Time");
define("LANG_CONTACTS_COMPANY_SLABOX_REMOVEPLAN","Remove SLA Plan from Company");
define("LANG_CONTACTS_COMPANY_SLABOX_NOPLAN","No SLA plan.");
define("LANG_CONTACTS_COMPANY_SLABOX_SELECTPLAN_NONE","- none -");
define("LANG_CONTACTS_COMPANY_SLABOX_CALENDAR", "Use calendar or enter <b><i>mm/dd/yy</i></b>.  Leave blank for no expiration.");

define("LANG_CONTACTS_COMPANY_ASIGNCONTACT_HEADER","Assign a New Company Contact");
define("LANG_CONTACTS_COMPANY_ASIGNCONTACT_INSTRUCTIONS","To assign a new contact to this company, enter/paste an<br><b><i>e-mail address</i></b> from the contact record below:<br><b>E-mail:</b>");
define("LANG_CONTACTS_COMPANY_ASIGNCONTACT_INSTRUCTIONS","To assign a new contact to this company, enter/paste an<br>
			<b><i>e-mail address</i></b> from the contact record below:<br>
			<b>E-mail:</b>");
define("LANG_CONTACTS_COMPANY_ASIGNCONTACT_SUBMIT","Zuordnen");
define("LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED","With selected contact:");
define("LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_NOTHING"," - do nothing - ");
define("LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_UNASSIGN","Unassign from company");
define("LANG_CONTACTS_COMPANY_CONTACTS_WITHSELECTED_UPDATE","Update");
define("LANG_CONTACTS_BACK_TO_LIST","Back to Contacts List");
define("LANG_CONTACTS_COMPANY_INSTRUCTIONS","Companies group together the Registered Contacts with whom you communicate.  To ensure 
various companies get the appropriate level of service according to your relationship with them, you can assign a 
Service Level Agreement (SLA) plan to the company. An SLA will allow Cerberus Helpdesk to automatically manage 
the ticket and response due dates for communication with Registered Contacts under a given company.");
define("LANG_CONTACTS_COMPANY_INSTRUCTIONS_VIEW","To delete a company, you must first unassign all its registered contacts.");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS","A Registered Contact is a user who has access to any 
public customer services (Cerberus Support Center, etc.) that you may be providing to customers through 
your various websites.  You can group all correspondence from this contact by adding their various e-mail addresses 
to their record.  By adding the contact to a company, you can tie all correspondence to a company record -- and the 
contact may further benefit from company-wide Service Level Agreements (SLAs), etc.");
define("LANG_CONTACTS_REGISTRED_INSTRUCTIONS_VIEW","To delete a registered contact, 
unassign all e-mail addresses from them.");
define("LANG_CONTACTS_COMPANY_ADD","Add Company");
define("LANG_CONTACTS_COMPANY_ERROR_ALREADY_ASSIGNED", "ERROR: Contact is already assigned to a company.");
define("LANG_CONTACTS_COMPANY_ERROR_CONTACT_NOT_EXIST", "ERROR: Contact does not exist.");
define("LANG_CONTACTS_COMPANY_INSTRUCTIONS","Companies group together the Registered Contacts with whom you communicate. To ensure various companies get the appropriate level of service according to your relationship with them, you can assign a Service Level Agreement (SLA) plan to the company. An SLA will allow Cerberus Helpdesk to automatically manage the ticket and response due dates for communication with Registered Contacts under a given company.");
define("LANG_CONTACTS_COMPANY_ASSIGNED_SUCCESS", "Success!  Contact assigned to company.");
define("LANG_CONTACTS_REGISTRED_UPDATE_SUCCESS", "Success!  Contact updated.");
define("LANG_CONTACTS_COMPANY_UPDATE_SUCCESS", "Success!  Company updated.");
define("LANG_CONTACTS_ADDRESS_ASSIGN_SUCCESS", "Success!  Address assigned to Contact.");
define("LANG_CONTACTS_ERROR_NO_ADDRESS_GIVEN", "ERROR: An e-mail address is required.");

// Reports
define("LANG_REPORTS_HEADING","Reports");
define("LANG_REPORTS_INSTRUCTIONS","Click on a link below to generate a report.");
define("LANG_REPORTS_SYSTEMREPORTS","System Reports");
define("LANG_REPORTS_NOREPORTSAVAILABLE","No reports available.");

// Name and Description of different Reports
define("LANG_PREF_SUCCESS","成功：参数已更新！");

// Update Ticket Strings
define("LANG_UPDATE_TICKET","更新派工单");
define("LANG_UPDATE_RECIPIENTS","派工单收件人");
define("LANG_UPDATE_REQUESTOR","派工单申请者");
define("LANG_UPDATE_CC","抄送");
define("LANG_UPDATE_CC_ADMIN","管理责抄送");
define("LANG_UPDATE_CC_QUEUE","队列抄送");
define("LANG_UPDATE_TIME","已工作时间");
define("LANG_UPDATE_TYPE","更新类型");
define("LANG_UPDATE_RESPONSE","答复申请者");
define("LANG_UPDATE_COMMENT","注释 (没有发送给申请者)");
define("LANG_UPDATE_SUBMIT","更新派工单");
define("LANG_UPDATE_ATTACHMENT","发送附件");
define("LANG_UPDATE_RESOLVED", "Set Resolved");
define("LANG_UPDATE_SPELLCHECK", "Spellcheck");
define("LANG_UPDATE_SPELLCHECK_ONLINE", "Spellcheck via cerberusweb.com");
define("LANG_UPDATE_TAKE_TICKET", "Take Ticket");
define("LANG_UPDATE_QUOTE", "Quote");
define("LANG_UPDATE_INSERT_SIGNATURE", "Insert Signature");
define("LANG_UPDATE_USE_TEMPLATE", "Use E-mail Template");
define("LANG_UPDATE_NO_ATTACHMENTS", "No Attachments");
define("LANG_UPDATE_EDIT_ATTACHMENTS", "Add/Remove Attachments");
define("LANG_UPDATE_NEXT_STEP", "Next Step, Go To");
define("LANG_UPDATE_NEXT_DETAILS", "Ticket #%s Details");
define("LANG_UPDATE_NEXT_QUEUE", "%s Queue List");
define("LANG_UPDATE_NEXT_LAST_SEARCH", "Last Search Performed");
define("LANG_UPDATE_NEXT_BATCHED_TICKETS", "Batched Tickets");
define("LANG_SEARCH_COMPANY", "Company Matches");

// Search Strings
define("LANG_SEARCH_TITLE","快速派工单搜索");
define("LANG_SEARCH_INQUEUE","在队列内");
define("LANG_SEARCH_STATUS","匹配状态");
define("LANG_SEARCH_ANY_COMPANY","any company");
define("LANG_SEARCH_ANY_STATUS","any status");
define("LANG_SEARCH_ANY_OWNER","any owner");
define("LANG_SEARCH_ANY_QUEUE","any queue");
define("LANG_SEARCH_ANY_ACTIVE","任何在活动中");
define("LANG_SEARCH_SENDER","发件人匹配");
define("LANG_SEARCH_OWNER","所有者匹配");
define("LANG_SEARCH_SUBJECT","题目匹配");
define("LANG_SEARCH_CONTENT","内容匹配");
define("LANG_SEARCH_ADVANCED", "Advanced Search Mode");
define("LANG_SEARCH_QUICK", "Quick Search Mode");
define("LANG_SEARCH_SUBMIT","开始搜索");

// Ticket List Strings
define("LANG_LIST_ACTIVE","在活动中的派工单");
define("LANG_LIST_MODIFY","更改选择");
define("LANG_LIST_CHANGE_OWNER","更改所有者");
define("LANG_LIST_CHANGE_STATUS","更改状态");
define("LANG_LIST_NEW_VIEW", "New view");
define("LANG_LIST_EDIT_VIEW", "Edit view");
define("LANG_LIST_CHANGE_QUEUE","更改队列");

// [ System Status Bar ]
define("LANG_STATUS_TITLE","系统状态");
define("LANG_STATUS_TICKET_TITLE","最近打开的派工单");
define("LANG_STATUS_TICKET_NONE","没有活动中的派工单");
define("LANG_STATUS_QUEUE_LOAD","队列装载%");
define("LANG_STATUS_LAST_TITLE","最后7天（派工单）");
define("LANG_STATUS_STATS_TITLE","统计表");
define("LANG_STATUS_STATS_TICKET_STORED","储存派工单的数目");
define("LANG_STATUS_STATS_EMAIL_STORED","储存通信的数量");
define("LANG_STATUS_STATS_ADDRESS_STORED","储存地址的数目");

// Ticket actions
define("LANG_ACTION_PROMPT", "Perform action?");
define("LANG_ACTION_MARK_AS_SPAM", "Mark as spam");
define("LANG_ACTION_MARK_AS_HAM", "Mark as not spam");
define("LANG_ACTION_MERGE_INSTRUCTIONS", "Merging tickets combines the correspondence, comments and requesters of multiple tickets into a single ticket id.  This can be used in a variety of ways, including consolidating multiple related tickets from a single user or company.  When merged, this newest ticket will disappear and all future correspondence will be handled through the oldest ticket.");
define("LANG_ACTION_BLOCK_SENDER", "Block sender");
define("LANG_ACTION_UNBLOCK_SENDER", "Unblock sender");
define("LANG_ACTION_BATCH_ADD", "Add to batch");
define("LANG_ACTION_BATCH_REMOVE", "Remove from batch");
define("LANG_ACTION_MERGE", "Merge ticket");
define("LANG_ACTION_CLONE", "Clone ticket");
define("LANG_ACTION_PRINT", "Print ticket");
define("LANG_ACTION_MERGE_INSTRUCTIONS", "Merging tickets combines the correspondence, comments and requesters of 
          			multiple tickets into a single ticket id.  This can be used in a variety of ways, including consolidating 
          			multiple related tickets from a single user or company.  When merged, this newest ticket will disappear and all 
          			future correspondence will be handled through the oldest ticket.");
define("LANG_ACTION_MERGE_PROMPT_BEFORE", "Merge Ticket #");
define("LANG_ACTION_MERGE_PROMPT_AFTER", " with Ticket #");
define("LANG_ACTION_MERGE_PROMPT_SUBMIT", "Merge!");
define("LANG_ACTION_MERGE_SURE", "Are you sure you want to merge this ticket into another ticket?  This action is irreversible.");
define("LANG_ACTION_PRINT_HIDE_CUSTOM_FIELDS", "Hide Custom Fields"); 
define("LANG_ACTION_PRINT_TITLE", "Cerberus :: Print Ticket");
define("LANG_ACTION_PRINT_SUBMIT", "Print");
define("LANG_ACTION_PRINT_CLOSE_WINDOW", "Close Window");
define("LANG_ACTION_MANAGE_REQUESTERS_HEADER", "Manage Ticket Requesters");
define("LANG_ACTION_MANAGE_REQUESTERS_INSTRUCTIONS", "Ticket Requesters automatically receive a copy of all correspondence regarding this ticket.<br>Suppressed requesters do not receive ticket updates.");
define("LANG_ACTION_MANAGE_REQUESTERS_REQUESTER_ADDRESS", "Requester Address");
define("LANG_ACTION_MANAGE_REQUESTERS_SUPPRESS", "Suppress");
define("LANG_ACTION_MANAGE_REQUESTERS_ADD", "Add Requester:");
define("LANG_ACTION_MANAGE_REQUESTERS_ADD_INSTRUCTIONS", "(format as an e-mail address, e.g.: customer@company.com)");
define("LANG_ACTION_MANAGE_REQUESTERS_PRIMARY", "PRIMARY");

// Information about what users are currently doing with a ticket
define("LANG_ACTION_USER_BROWSING", "browsing");
define("LANG_ACTION_USER_REPLYING", "REPLYING");
define("LANG_ACTION_USER_COMMENTING", "commenting");

// My Cerberus
define("LANG_MYCERBERUS", "My Cerberus");
define("LANG_MYCERBERUS_CUSTOMSETTINGSFOR", "Custom settings for");
define("LANG_MYCERBERUS_HEADERS_DASHBOARD", "Dashboard");
define("LANG_MYCERBERUS_HEADERS_PREFERENCES", "Preferences");
define("LANG_MYCERBERUS_HEADERS_NOTIFICATION", "Notification ");
define("LANG_MYCERBERUS_HEADERS_QUICKASSIGN", "Quick Assign/Watcher");
define("LANG_MYCERBERUS_LAYOUT_ENABLED_MODULES", "Enabled Modules");
define("LANG_MYCERBERUS_LAYOUT_DISABLED_MODULES", "Disabled Modules");
define("LANG_MYCERBERUS_LAYOUT_PAGE_DISPLAY", "Page: Ticket Display");
define("LANG_MYCERBERUS_HEADERS_PROJECTS", "Projects/Tasks");
define("LANG_MYCERBERUS_HEADERS_PMS", "Private Messages");
define("LANG_MYCERBERUS_DASHBOARD_MYPERFORMANCE", "My Performance");
define("LANG_MYCERBERUS_DASHBOARD_ASSIGNEDACTIVE", "Assigned Active Tickets:");
define("LANG_MYCERBERUS_DASHBOARD_OLDESTASSIGNED", "Oldest Assigned Ticket:");
define("LANG_MYCERBERUS_DASHBOARD_LASTRESOLVED", "Last Resolved Ticket:");
define("LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY", "7 Day Activity Snapshot:");
define("LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_REPLIESCOMMENTS", "(replies/comments)");
define("LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_NODATA", "No data available.");
define("LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_EMAIL", "EMail");
define("LANG_MYCERBERUS_DASHBOARD_7DAYACTIVITY_COMMENTS", "Comments");
define("LANG_MYCERBERUS_DASHBOARD_TICKETHISTORY_NO", "No ticket history for this date.");
define("LANG_MYCERBERUS_DASHBOARD_LISTMYACTIVE", "List My Active Tickets");
define("LANG_MYCERBERUS_DASHBOARD_OFACTIVETICKETS", "active tickets");
define("LANG_MYCERBERUS_NOTIFICATIONS_HEADER", "My Notification Settings");
define("LANG_MYCERBERUS_NOTIFICATIONS_INSTRUCTIONS", "Here you can set up <B>notifications</B> that will send you a configurable e-mail notice when certain events occur, such as a new ticket being assigned to you.");
define("LANG_MYCERBERUS_NOTIFICATIONS_INSTRUCTIONS", "Here you can set up <B>notifications</B> that will send you a configurable e-mail 
					notice when certain events occur, such as a new ticket being assigned to you.");
define("LANG_MYCERBERUS_NOTIFICATIONS_TOKENS_INSTRUCTIONS", "		            You can use the following tokens in all notification templates:<br>
		            <B>##ticket_id##</B> - Ticket ID or Mask<br>
		            <B>##ticket_subject##</B> - Ticket Subject<br>
		            <B>##ticket_status##</B> - Ticket Status (new, resolved, etc.)<br>
		            <B>##ticket_owner##</B> - Ticket Owner User Name<br>
		            <B>##ticket_email##</B> - The Email Body of the Original Ticket Message<br>
		            <B>##queue_name##</B> - Ticket Queue Name (Support, etc.)<br>
		            <B>##requester_address##</B> - The Email Address that Opened the Ticket.<br>");
define("LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE", "E-mail Template:");
define("LANG_MYCERBERUS_NOTIFICATIONS_ENABLED", "Enabled:");
define("LANG_MYCERBERUS_NOTIFICATIONS_SENDTO", "Send to:");
define("LANG_MYCERBERUS_NOTIFICATIONS_TOKENS_INSTRUCTIONS", " You can use the following tokens in all notification templates:<br><B>##ticket_id##</B> - Ticket ID or Mask<br><B>##ticket_subject##</B> - Ticket Subject<br><B>##ticket_status##</B> - Ticket Status (new, resolved, etc.)<br><B>##ticket_owner##</B> - Ticket Owner Agent Name<br><B>##ticket_email##</B> - The Email Body of the Original Ticket Message<br><B>##queue_name##</B> - Ticket Queue Name (Support, etc.)<br><B>##requester_address##</B> - The Email Address that Opened the Ticket.<br>");
define("LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED", "(comma-delimited email addresses)");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_HEADER", "Event: New Ticket");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUES", "Queues:");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUE", "Queue");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_QUEUES_INFO", "(the queues you want<br>to be notified of new<br>tickets in)<br>");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_ASSIGNMENT_HEADER", "Event: Assignment");
define("LANG_MYCERBERUS_NOTIFICATIONS_EVENT_CLIENTREPLY_HEADER", "Event: Client Reply on Assigned Ticket");
define("LANG_MYCERBERUS_PMS_VIEW_HEADER", "Private Messages in ");
define("LANG_MYCERBERUS_PMS_VIEW_NO", "No private messages.");
define("LANG_MYCERBERUS_PMS_VIEW_MARK_READ", "Mark Read");
define("LANG_MYCERBERUS_PMS_VIEW_MARK_UNREAD", "Mark Unread");
define("LANG_MYCERBERUS_PMS_VIEW_MARK_DELETE", "Delete Checked");
define("LANG_MYCERBERUS_PMS_VIEW_CHANGEFOLDER", "Change Folder:");
define("LANG_MYCERBERUS_PMS_VIEW_INBOX", "Inbox");
define("LANG_MYCERBERUS_PMS_VIEW_SENT", "Sent");
define("LANG_MYCERBERUS_PMS_SEND_HEADER", "Send a Private Message");
define("LANG_MYCERBERUS_PMS_SEND_TOUSER", "To User:");
define("LANG_MYCERBERUS_PMS_SEND_SUBJECT", "Subject:");
define("LANG_MYCERBERUS_PMS_SEND_MESSAGE", "Message:");
define("LANG_MYCERBERUS_PROJECTS_HEADER", "Projects");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_HEADER", "Active Projects");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_NAME", "Project Name");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_TOTAL", "Total Tasks");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_INCOMPLETE", "Incomplete");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_COMPLETE", "Complete");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_MANAGER", "Project Manager");
define("LANG_MYCERBERUS_PROJECTS_ACTIVE_HIDECOMPLETED", "Hide Completed Projects");
define("LANG_MYCERBERUS_PROJECTS_CREATE_HEADER", "Create New Project");
define("LANG_MYCERBERUS_PROJECTS_CREATE_PROJECTNAME", "Project Name:");
define("LANG_MYCERBERUS_PROJECTS_CREATE_PROJECTNAME_INSTRUCTIONS", "(<b>For example:</b> \"To Do List\" or \"Bug Reports\")");
define("LANG_MYCERBERUS_PROJECTS_CREATE_MANAGER", "Manager:");
define("LANG_MYCERBERUS_PROJECTS_CREATE_RESOURCES", "Resources:");
define("LANG_MYCERBERUS_PROJECTS_CREATE_MEMBERS", "Project Members");
define("LANG_MYCERBERUS_PROJECTS_CREATE_AVAILABLE", "Available Users");
define("LANG_MYCERBERUS_PROJECTS_VIEW_HEADER", "Project:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_PROJECTTASKLIST", "project task list");
define("LANG_MYCERBERUS_PROJECTS_VIEW_NEWTASK", "new task");
define("LANG_MYCERBERUS_PROJECTS_VIEW_RETURNTOLIST", "return to project list");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_HEADER", "Project Details");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_ASSIGNALL", "Assign All");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_UNASSIGNALL", "Unassign All");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES", "Categories:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE", "Delete");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE_INFO", "Permanently delete this project.");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_DELETE_SURE", "CERBERUS: Are you sure you want to permanently delete this project and all its tasks?");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_TASKCATEGORYNAME", "Task Category Name");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_ADD", "Add Category:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_CATEGORIES_ADD_INFO", "(such as: \"<b>Bug Reports</b>\")");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_TOGGLE_BRIEF", "toggle panel brief mode");
define("LANG_MYCERBERUS_PROJECTS_VIEW_DETAILS_TOGGLE_ADVANCED", "toggle panel advanced mode");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_HEADER", "Tasks in Project");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_TASKNAME", "Task Name");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_CATEGORY", "Category");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ASSIGNED", "Assigned");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_PRIORITY", "Priority");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_UPDATED", "Updated");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_DUE", "Due");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_NOTES", "Notes");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_PROGRESS", "Progress");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_NOTASKS", "No tasks in project or matching filters.");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ONLYINCATEGORY", "Only in Category:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ANYCATEGORY", "- any category -");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_HIDECOMPLETED", "Hide Completed");
define("LANG_MYCERBERUS_PROJECTS_VIEW_TASKS_ONLYMY", "Only Show My Tasks");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_HEADER", "Create Task in Project");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY", "Summary:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_SUMMARY_INSTRUCTIONS", "(<b>For example:</b> \"Install +1GB Memory in Bob's Workstation\")");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY", "Category:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO", "Assigned To:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_NOBODY", "Nobody");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DUE", "Task Due:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_REMINDER", "Reminder:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DESCRIPTION", "Description:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_CATEGORY_NONE", "None (Top Level)");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PROGRESS", "Progress:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_ASSIGNEDTO_PRIORITY", "Priority:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_CALENDAR", "pop-up calendar");
define("LANG_MYCERBERUS_PROJECTS_VIEW_CREATETASK_DATES_INSTRUCTIONS", "-or- enter as <b>mm/dd/yy</b>, e.g. 01/15/05)");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_HEADER", "Task:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_RETURNTOPROJECT", "return to project details");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_RETURNTOPROJECTLIST", "return to project list");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_INFO", "To edit this task's details you must be the project manager or the assigned user.");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE", "Delete:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE_INSTRUCTIONS", "Permanently delete this task.");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_DELETE_SURE", "CERBERUS: Are you sure you want to permanently delete this task?");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_HEADER", "Task Notes");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_POSTER", "Poster:");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_NONE", "No task notes.");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_DELETE_SURE", "CERBERUS: Are you sure you want to permanently delete this task note?");
define("LANG_MYCERBERUS_PROJECTS_VIEW_VIEWTASK_TASKNOTES_ADD_HEADER", "Add Task Note");
define("LANG_MYCERBERUS_QUICKASSIGN_HEADER", "Quick Assign &amp; Watcher Settings");
define("LANG_MYCERBERUS_QUICKASSIGN_INSTRUCTIONS", "The <B>quick assign</B> column below determines which queues will be used by the quick assign feature in the header above. Choose the queues you would like to have tickets assigned to you from. For example: assign from the queues '<i><B>Support</B></i>' and '<i><B>Sales</B></i>', but don't assign from the queues '<i><B>Failure Notices</B></i>' or '<i><B>Virus/SPAM</B></i>'.<br><br>As a <B>watcher</B> you will receive an e-mail copy of all new tickets and correspondence for the queues you select below. &nbsp;A watcher can read and reply to tickets through any e-mail client, or simply use the e-mail messages as a notification to log into the GUI.<br><br>Your watcher e-mails will be sent to:");
define("LANG_MYCERBERUS_QUICKASSIGN_QUEUE", "Queue");
define("LANG_MYCERBERUS_QUICKASSIGN_QUICKASSIGN", "Quick Assign");
define("LANG_MYCERBERUS_QUICKASSIGN_WATCHER", "Watcher");
define("LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_QUICKASSIGN", "Quick Assign:");
define("LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_TEXT1", "Don't assign me more than");
define("LANG_MYCERBERUS_QUICKASSIGN_NOMORETHAN_TEXT2", "ticket(s) at once.");

// Information about what online users are doing
define("LANG_ONLINE_AUTH", "is authenticating");
define("LANG_ONLINE_MAIN_SCREEN", "on the main screen");
define("LANG_ONLINE_LISTING_TICKETS", "is listing tickets in <b>%s</b>");
define("LANG_ONLINE_LISTING_RESULTS", "is listing search results");
define("LANG_ONLINE_DISPLAY_TICKET", "is displaying ticket <a href=\"%s\" class=\"cer_whos_online_text\"><b>#%s</b></a>");
define("LANG_ONLINE_REPLY_TICKET", "is replying to ticket <a href=\"%s\" class=\"cer_whos_online_text\"><b>#%s</b></a>");
define("LANG_ONLINE_COMMENT_TICKET", "is commenting on ticket <a href=\"%s\" class=\"cer_whos_online_text\"><b>#%s</b></a>");
define("LANG_ONLINE_CONFIGURATION", "is in configuration");
define("LANG_ONLINE_CREATE_TICKET", "is creating a new ticket");
define("LANG_ONLINE_BROWSE_KB", "is browsing the knowledgebase");
define("LANG_ONLINE_PREF_EDIT", "is editing their preferences");
define("LANG_ONLINE_CONFIG_TICKET_VIEWS", "is configuring ticket views");
define("LANG_ONLINE_MY_CERBERUS", "is in My Cerberus");
define("LANG_ONLINE_TASKS", "is in project management");
define("LANG_ONLINE_PM", "is in private messages");

// Information about the SPAM-status of a ticket
define("LANG_TICKET_IS_SPAM", "Marked as Spam");
define("LANG_TICKET_IS_HAM", "Marked as not Spam");

// Training of Spam Detection System
define("LANG_TICKET_SPAM_TRAINING_IS","Ticket is Spam");
define("LANG_TICKET_SPAM_TRAINING_NOT","Ticket is Not Spam");

// For i18n of dates
// Various date formats used in the system
define("LANG_DATE_FORMAT_DAY_TOTALS", "<b>%a</b> (%b %e)");
define("LANG_DATE_FORMAT_STATUS_TITLE", "%a %b %e %Y %I:%M%P %Z");
define("LANG_DATE_FORMAT_STANDARD", "%a %b %e %Y %I:%M%P %Z");

// Short names for days, hours, minutes etc.
define("LANG_DATE_SHORT_SECONDS_ABBR", "sec");
define("LANG_DATE_SHORT_MINUTES_ABBR", "min");
define("LANG_DATE_SHORT_HOURS_ABBR", "hr");
define("LANG_DATE_SHORT_DAYS_ABBR", "days");

define("LANG_DATE_SHORT_SECONDS", "s");
define("LANG_DATE_SHORT_MINUTES", "m");
define("LANG_DATE_SHORT_HOURS", "h");
define("LANG_DATE_SHORT_DAYS", "d");

define("LANG_DATE_SHORT_SECOND", "s");
define("LANG_DATE_SHORT_MINUTE", "m");
define("LANG_DATE_SHORT_HOUR", "h");
define("LANG_DATE_SHORT_DAY", "d");

// Actions that can be performed on a thread
define("LANG_THREAD_COMMENT","Comment");
define("LANG_THREAD_FORWARD", "Forward");
define("LANG_THREAD_REPLY","Reply");
define("LANG_THREAD_QUOTE_FORWARD", "Quote &amp; Forward");
define("LANG_THREAD_QUOTE_REPLY", "Quote &amp; Reply");
define("LANG_THREAD_BOUNCE", "Bounce");
define("LANG_THREAD_ADD_TO_REQUESTERS", "Add to Requesters");
define("LANG_THREAD_STRIP_HTML", "Strip HTML");
define("LANG_THREAD_BLOCK_SENDER", "Block Sender");
define("LANG_THREAD_UNBLOCK_SENDER", "Unblock Sender");
define("LANG_THREAD_PRINT", "Print Thread");
define("LANG_STATUS_STATS_KB_STORED","知识库文件的数目");

?>
