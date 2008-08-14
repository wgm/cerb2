{literal}
<style>
<!--
TEXTAREA {
	width: 100%;
}

.cer_button_face { 
	font-family: 'Verdana', 'Arial', 'Helvetica', 'sans-serif'; 
	font-size: 8pt; 
	font-weight: normal; 
}

.cer_header_login {  
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	font-size: 10pt;
}

.cer_header_loginBold { 
	font-family: Verdana, Arial, Helvetica, sans-serif; 
	font-size: 10pt; 
	font-weight: bold;
}

.help_link
{
	CURSOR: help;
	TEXT-DECORATION: none
} 

.context_tooltip
{
	border: 1px solid #888888;
	background: #eeeeee;
	TEXT-DECORATION: none;
}
.searchSyntaxText
{
	font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; 
	font-size: 8pt; 
	font-weight: normal;
}

.cer_table_row_line { background: #333333; }

.cer_header_loginLink { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #666666; font-weight: bold; text-decoration: underline}
.cer_header_loginLink:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #666666; font-weight: bold; text-decoration: underline}
.cer_header_loginLink:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #666666; font-weight: bold; text-decoration: underline}
.cer_header_loginLink:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #FF9204; font-weight: bold; text-decoration: underline; background: #FFFFFF; }

.cer_queue_link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: bold; text-decoration: none; }
.cer_queue_link:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: bold; text-decoration: underline; }
.cer_queue_link:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: bold; text-decoration: underline; }
.cer_queue_link:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #333333; font-weight: bold; text-decoration: underline; background: #FFFFFF; }

.cer_queue_link_sm { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: normal; text-decoration: none; }
.cer_queue_link_sm:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: normal; text-decoration: underline; }
.cer_queue_link_sm:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #666666; font-weight: normal; text-decoration: underline; }
.cer_queue_link_sm:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #333333; font-weight: normal; text-decoration: underline; background: #FFFFFF; }

.cer_white_link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #FFFFFF; font-weight: bold; text-decoration: none; }
.cer_white_link:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #FFFFFF; font-weight: bold; text-decoration: underline; }
.cer_white_link:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; color: #FFFFFF; font-weight: bold; text-decoration: underline; }

.cer_system_status_bg1 { background: #CCCCCC; }
.cer_system_status_bg2 { background: #BABABA; }
.cer_refresh_box_bg { background: #000066; }

.cer_footer_text { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #333333; }
.cer_footer_green { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: bold; color: #00AA00; }
.cer_footer_red { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: bold; color: #FF0000; }
.cer_id_text { font-family: Courier New, sans-serif; font-size: 9pt ; font-weight: normal; color: #000000}

.priority_None { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #000000; }
.priority_Low { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #339900; }
.priority_Medium { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #0099ff; }
.priority_High { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: normal; color: #ff6600; }
.priority_Critical { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: bold; color: #ff0099; }
.priority_Emergency { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: bold; color: #ff0000; }

.cer_button_face_old { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: none; color: #333333; }

.cer_maintable_header { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none}
.cer_maintable_header2 { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none}
.cer_maintable_heading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #333333 }
.cer_maintable_headingSM { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: bold; color: #333333 }
.cer_maintable_text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333 }
.cer_maintable_new_correspondance { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #FF0000 }
.cer_maintable_text_1 { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333; background: #EEEEEE; }
.cer_maintable_text_2 { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333; background: #E0E0E0; }

.cer_custom_field_heading { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 8pt; font-weight: bold; color: #333333; }
.cer_custom_field_text { font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 9pt; font-weight: normal; color: #333333; }

.cer_maintable_subjectLink { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #333333 ; text-decoration: underline}
.cer_maintable_subjectLink:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #333333 ; text-decoration: underline}
.cer_maintable_subjectLink:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #333333 ; text-decoration: underline}
.cer_maintable_subjectLink:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #333333 ; text-decoration: underline; background: #FFFFFF;}

.cer_navbar_heading:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: bold; color: #333333 }
.cer_navbar_heading:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: bold; color: #333333 }
.cer_navbar_heading:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: bold; color: #000000; text-decoration: none; background: #FFFFFF; }

.cer_navbar_selected { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #FFFFFF; text-decoration: none; }

.cer_panel_heading_background { background: #0065CA; }

.cer_home_tickets_background { background: #33cc00; }
.cer_home_my_tickets_background { background: #FF6600; }
.cer_home_quicksearch_background { background: #666666; }
.cer_home_preferences_background { background: #0099FF; }
.cer_home_preferences_background_2 { background: #009900; }
.cer_home_preferences_background_3 { background: #FF6600; }
.cer_home_preferences_background_4 { background: #0079F2; }
.cer_home_preferences_background_5 { background: #03529D; }
.cer_home_templates_background { background: #65BD0D; }
.cer_create_ticket_background { background: #0099FF; }

.cer_settings_background { background: #888888; }
.cer_settings_background_2 { background: #65BD0D; }
.cer_settings_background_3 { background: #0079F2; }
.cer_settings_background_4 { background: #03529D; }
.cer_settings_background_5 { background: #505050; }
.cer_settings_background_6 { background: #E93700; }
.cer_settings_background_7 { background: #2291FF; }

.cer_whos_online_background { background: #0099FF; }
.cer_whos_online_text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: normal; color: #333333 }

.cer_display_header { font-family: Arial, Helvetica, sans-serif; font-size: 14pt; font-weight: bold; color: #000000}
.cer_display_staff_message { background: #505050; }
.cer_display_customer_message { background: #EE1111; }
.cer_display_staff_comment { background: #2291FF; }

.cer_display_commentLink { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #FFFF00; font-weight: bold; text-decoration: underline }
.cer_display_commentLink:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #FFFF00; font-weight: bold; text-decoration: underline }
.cer_display_commentLink:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; color: #FFFF00; font-weight: bold; text-decoration: underline }

.cer_display_emailText { font-family: Courier New, sans-serif; font-size: 10pt ; font-weight: normal; color: #000000}

.cer_display_file_link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: bold; color: #333333 }
.cer_display_file_link:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: bold; color: #333333 }
.cer_display_file_link:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt ; font-weight: bold; color: #333333 }

.cer_display_ticket_header { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold ; color: #FFFFFF}
.cer_display_thread_header { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold ; color: #FFFFFF}

.cer_display_viewsLink { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; font-weight: normal; color: #FF671D }
.cer_display_viewsLink:link { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; font-weight: normal; color: #FF671D }
.cer_display_viewsLink:visited { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt ; font-weight: normal; color: #FF671D }

.cer_display_batch_background { background: #03529D; }
.cer_display_glance_box_background { background: #0099FF; }
.cer_display_properties_box_background { background: #03529D; }
.cer_display_vitals_box_background { background: #0099FF; }
.cer_display_custom_fields_background { background: #888888; }
.cer_display_requesters_background { background: #03529D; }
.cer_display_escalation_background { background: #03529D; }
.cer_display_log_background { background: #5368A6; }
.cer_display_company_sla_background { background: #009900; }
.cer_display_suggestion_sla_background { background: #03529D; }
.cer_display_history_background { background: #FF671D; }
.cer_display_ticket_fields_background { background: #AFAFAF; }

.cer_display_reply_background {background: #0079F2; }

.cer_display_user { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: normal; color: #333333 }
.cer_display_user_red { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: normal; color: #FF0000 }

.cer_display_custom_field { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: normal; color: #333333 }

.cer_list_header { background: #0066CC }
.cer_list_text { font-family: Arial, Helvetica, sans-serif; font-size: 12pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none;}
.cer_list_ticket_header { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold ; color: #333333; background: #C0C0C0 }

.cer_config_menu_background { background: #0065CA; }
.cer_config_option_background { background: #65B01A; }

.cer_configuration_header { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF; font-style: normal; text-decoration: none}
.cer_configuration_updated { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #FF0000 }
.cer_configuration_success { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #009900 }

.cer_configuration_leftbox { background: #CCCCCC; }
.cer_configuration_leftbox_body { background: #CCCCCC; }
.cer_configuration_leftbox_link { font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; text-decoration: underline; font-weight: bold; color: #333333; }
.cer_configuration_leftbox_link:link { font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; text-decoration: underline; font-weight: bold; color: #333333; }
.cer_configuration_leftbox_link:visited { font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; text-decoration: underline; font-weight: bold; color: #333333; }
.cer_configuration_leftbox_link:hover { font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; text-decoration: underline; font-weight: bold; background: #EEEEEE; color: #000000; }

.cer_knowledgebase_background { background: #0079F2; }
.cer_knowledgebase_heading { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 12pt ; font-weight: bold; color: #000000 }
.cer_knowledgebase_comment { font-family: Courier, Helvetica, sans-serif; font-size: 10pt ; font-weight: normal; color: #000000 }

.cer_knowledgebase_link { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333 }
.cer_knowledgebase_link:link { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333; text-decoration: underline; }
.cer_knowledgebase_link:visited { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #333333; text-decoration: underline; }
.cer_knowledgebase_link:hover { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 9pt ; font-weight: normal; color: #FF9204 }

.cer_knowledgebase_link_lg { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #222222 }
.cer_knowledgebase_link_lg:link { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #222222; text-decoration: underline; }
.cer_knowledgebase_link_lg:visited { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #222222; text-decoration: underline; }
.cer_knowledgebase_link_lg:hover { font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #FF9204 }

.cer_knowledgebase_text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: normal; color: #333333 }
.cer_knowledgebase_article { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #333333 }
.cer_knowledgebase_article:hover { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt ; font-weight: bold; color: #FF9204 }

.menu_style {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF}

.menu_style {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #FFFFFF}
.headerMenu {
	color: #EEEEEE;
	text-decoration: underline;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
.headerMenuActive {
	color: #FFFFFF;
	text-decoration: none;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
}

-->
</style>
{/literal}