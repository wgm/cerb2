    <table width="100%" border="0" cellspacing="1" cellpadding="1">
    
		  <tr class="boxtitle_gray_glass_dk"> 
            <td nowrap align="center">- Configuration -</td>
          </tr>
		  <tr class="cer_configuration_leftbox_body"> 
            <td>
				<img src="includes/images/config/icon_configuration.gif" border="0" align="absmiddle">
            	<a href="<?php echo cer_href("configuration.php"); ?>" class="cer_configuration_leftbox_link">Home</a>
            </td>
          </tr>
          
		  <tr>
		  	<td><img src="includes/images/spacer.gif" height="5" width="1" border="0"></td>
		  </tr>
          
		  <tr class="boxtitle_gray_glass_dk"> 
	        <td align="center" nowrap>- Helpdesk -</td>
	      </tr>

		  <?php if($priv->has_priv(ACL_GLOBAL_SETTINGS,BITGROUP_2) || $priv->has_priv(ACL_UPLOAD_LOGO,BITGROUP_1)) { ?>
		  <tr class="boxtitle_blue_glass"> 
            <td nowrap>
            	<img src="includes/images/config/icon_settings.gif" border="0" align="absmiddle">
            	Settings
            </td>
          </tr>
          
							  <?php if($priv->has_priv(ACL_GLOBAL_SETTINGS,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=settings"); ?>" class="cer_configuration_leftbox_link">Global Settings</a></td>
          </tr>
								<?php } ?>
							  <?php if($priv->has_priv(ACL_GLOBAL_SETTINGS,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=statuses"); ?>" class="cer_configuration_leftbox_link">Ticket Statuses</a></td>
          </tr>
								<?php } ?>
								<?php /*
								<?php if($priv->has_priv(ACL_OPTIONS_UPLOAD_KEY,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=key"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_PRODUCT_KEY; ?></a></td>
          </tr>
								<?php } ?>
								*/ ?>
								<?php if($priv->has_priv(ACL_UPLOAD_LOGO,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=branding"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_BRANDING_UPLOAD; ?></a></td>
          </tr>
								<?php } ?>
				<?php } ?>

      <?php if($priv->has_priv(ACL_QUEUE_CREATE,BITGROUP_1) || $priv->has_priv(ACL_QUEUE_EDIT,BITGROUP_1) || $priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1)) {  ?>
		  <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_queues.gif" border="0" align="absmiddle">
            	<?php echo LANG_WORD_QUEUES; ?>
            </td>
          </tr>
          
      	<?php if($priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2)) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=schedules"); ?>" class="cer_configuration_leftbox_link">Manage Schedules</a></td>
          </tr>
					<?php } ?>
					
								<?php if($priv->has_priv(ACL_QUEUE_CREATE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=queues&pqid=0"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_QUEUE_NEW; ?></a></td>
          </tr>
								<?php } ?>

								<?php if($priv->has_priv(ACL_QUEUE_EDIT,BITGROUP_1) || $priv->has_priv(ACL_QUEUE_DELETE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=queues"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_QUEUE_EDIT; ?></a></td>
          </tr>
								<?php } ?>

								<?php if($priv->has_priv(ACL_QUEUE_CATCHALL,BITGROUP_3)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=queue_catchall"); ?>" class="cer_configuration_leftbox_link"><?php echo "Queue Catch-All Rules"; ?></a></td>
          </tr>
								<?php } ?>
								
								
		<?php } ?>
			
		<?php if($priv->has_priv(ACL_USER_CREATE,BITGROUP_1) || $priv->has_priv(ACL_USER_EDIT,BITGROUP_1) || $priv->has_priv(ACL_USER_DELETE,BITGROUP_1)) {  ?>
		  <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_agents.gif" border="0" align="absmiddle">
            	<?php echo LANG_CONFIG_MENU_USERS; ?>
            </td>
          </tr>
          <?php if($priv->has_priv(ACL_USER_CREATE,BITGROUP_1)) { ?>
								<tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=users&puid=0"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_USERS_NEW; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_USER_EDIT,BITGROUP_1) || $priv->has_priv(ACL_USER_DELETE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=users"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_USERS_EDIT; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_GROUPS_CREATE,BITGROUP_1) || $priv->has_priv(ACL_GROUPS_EDIT,BITGROUP_1) || $priv->has_priv(ACL_GROUPS_DELETE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=groups"); ?>" class="cer_configuration_leftbox_link">Manage Groups &amp; Permissions</a></td>
          </tr>
								<?php } ?>
				<?php } ?>
				
				
<?php /* if($priv->has_priv(ACL_CUSTOM_FIELDS,BITGROUP_2)) { */  ?>
		<tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_plugins.gif" border="0" align="absmiddle">            
            	<?php echo LANG_CONFIG_PLUGINS; ?>
            </td>
        </tr>
          
		<?php /* if($priv->has_priv(ACL_CUSTOM_FIELDS,BITGROUP_2)) { */ ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=plugins"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_PLUGINS_MANAGE; ?></a></td>
          </tr>
		<?php /* } */ ?>
<?php /* } */ ?>
			
<?php if($priv->has_priv(ACL_CUSTOM_FIELDS,BITGROUP_2)) { ?>
		<tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_custom_fields.gif" border="0" align="absmiddle">
            	<?php echo LANG_CONFIG_CUSTOM_FIELDS; ?>
            </td>
        </tr>
          
		<?php if($priv->has_priv(ACL_CUSTOM_FIELDS,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=custom_fields"); ?>" class="cer_configuration_leftbox_link">Custom Field Groups</a></td>
          </tr>
		<?php } ?>
		
		<?php if($priv->has_priv(ACL_CUSTOM_FIELDS,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=custom_field_bindings"); ?>" class="cer_configuration_leftbox_link">Custom Field Bindings</a></td>
          </tr>
		<?php } ?>
<?php } ?>
			
	  <?php if($priv->has_priv(ACL_SLA_PLANS,BITGROUP_2)
	  	|| $priv->has_priv(ACL_SLA_SCHEDULES,BITGROUP_2)) { ?>
		  <tr class="boxtitle_blue_glass"> 
			<td nowrap>
				<img src="includes/images/config/icon_sla.gif" border="0" align="absmiddle">
				Service Level (SLA)
			</td>
          </tr>
          
      <?php if($priv->has_priv(ACL_SLA_SCHEDULES,BITGROUP_2)) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=schedules"); ?>" class="cer_configuration_leftbox_link">Manage SLA Schedules</a></td>
          </tr>
					<?php } ?>
      <?php if($priv->has_priv(ACL_SLA_PLANS,BITGROUP_2)) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=sla"); ?>" class="cer_configuration_leftbox_link">Manage SLA Plans</a></td>
          </tr>
					<?php } ?>
					
			<?php } ?>
			
      <?php if($priv->has_priv(ACL_REINDEX_ARTICLES,BITGROUP_2) || $priv->has_priv(ACL_REINDEX_THREADS,BITGROUP_2)) {  ?>
		  <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_search_index.gif" border="0" align="absmiddle">
            	Search Indexes
            </td>
          </tr>
								<?php if($priv->has_priv(ACL_REINDEX_THREADS,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=search_index&action=threads"); ?>" class="cer_configuration_leftbox_link">Reindex E-mail Threads</a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_REINDEX_ARTICLES,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=search_index&action=articles"); ?>" class="cer_configuration_leftbox_link">Reindex Knowledgebase Articles</a></td>
          </tr>
								<?php } ?>
			<?php } ?>

      <?php if(($priv->has_priv(ACL_KB_CATEGORY_CREATE,BITGROUP_1) || 
      			$priv->has_priv(ACL_KB_CATEGORY_EDIT,BITGROUP_1) || 
      			$priv->has_priv(ACL_KB_CATEGORY_DELETE,BITGROUP_1) ||
      			$priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2)) && $cfg->settings["show_kb"]) { ?>
		  <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_knowledgebase.gif" border="0" align="absmiddle">
            	<?php echo LANG_WORD_KNOWLEDGEBASE; ?>
            </td>
          </tr>
								<?php if($priv->has_priv(ACL_KB_CATEGORY_CREATE,BITGROUP_1) && $cfg->settings["show_kb"]) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=kbase&pkbid=0"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_KBCAT_NEW; ?></a></td>
          </tr>
								<?php } ?>
								<?php if(($priv->has_priv(ACL_KB_CATEGORY_EDIT,BITGROUP_1) || $priv->has_priv(ACL_KB_CATEGORY_DELETE,BITGROUP_1)) && $cfg->settings["show_kb"]) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=kbase"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_KBCAT_EDIT; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_KB_COMMENT_EDITOR,BITGROUP_2) && $cfg->settings["show_kb"]) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=kbase_comments"); ?>" class="cer_configuration_leftbox_link">Approve/Reject Article Comments</a></td>
          </tr>
								<?php } ?>
				<?php } ?>
      <?php if($priv->has_priv(ACL_MAINT_OPTIMIZE,BITGROUP_1) || $priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1)) { ?>
          <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_maintenance.gif" border="0" align="absmiddle">
            	<?php echo LANG_CONFIG_PURGE_TITLE; ?>
            </td>
          </tr>
          			<?php if($priv->has_priv(ACL_MAINT_OPTIMIZE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=maintenance_optimize"); ?>" class="cer_configuration_leftbox_link">Optimize Database</a></td>
          </tr>
          			<?php } ?>
          			<?php if($priv->has_priv(ACL_MAINT_OPTIMIZE,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=maintenance_repair"); ?>" class="cer_configuration_leftbox_link">Repair Database</a></td>
          </tr>
          			<?php } ?>
								<?php if($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=maintenance"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_MAINT_PURGE; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=maintenance_attachments"); ?>" class="cer_configuration_leftbox_link">Clean-up Attachments</a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_MAINT_PURGE_DEAD,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=maintenance_tempdir"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_PURGE_TEMPDIR; ?></a></td>
          </tr>
								<?php } ?>
        <?php } ?>
        
				<?php if($priv->has_priv(ACL_OPTIONS_FEEDBACK,BITGROUP_1)
                  || $priv->has_priv(ACL_OPTIONS_REPORT_BUG,BITGROUP_1)) { ?>
          <tr class="boxtitle_blue_glass"> 
            <td nowrap>
				<img src="includes/images/config/icon_development.gif" border="0" align="absmiddle">
            	Development
            </td>
          </tr>
								<?php if($priv->has_priv(ACL_OPTIONS_REPORT_BUG,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=bug"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_BUG; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_OPTIONS_FEEDBACK,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=feedback"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_FEEDBACK; ?></a></td>
          </tr>
								<?php } ?>
				<?php } ?>
				
		  <tr>
		  	<td><img src="includes/images/spacer.gif" height="5" width="1" border="0"></td>
		  </tr>
		
		  <tr class="boxtitle_gray_glass_dk"> 
	        <td align="center" nowrap>- Parser -</td>
	      </tr>

			<?php if($priv->has_priv(ACL_MAILRULE_CREATE,BITGROUP_2) 
		      || $priv->has_priv(ACL_MAILRULE_EDIT,BITGROUP_2)
		      || $priv->has_priv(ACL_MAILRULE_DELETE,BITGROUP_2)
              || $priv->has_priv(ACL_PARSER_LOG,BITGROUP_2)) { ?>
		  <tr class="boxtitle_blue_glass"> 
			<td nowrap>
				<img src="includes/images/config/icon_parser.gif" border="0" align="absmiddle">
				Parser Settings
			</td>
          </tr>
      <?php if($priv->has_priv(ACL_MAILRULE_CREATE,BITGROUP_2) 
		      || $priv->has_priv(ACL_MAILRULE_EDIT,BITGROUP_2)
              || $priv->has_priv(ACL_MAILRULE_DELETE,BITGROUP_2)
              || $priv->has_priv(ACL_EMAIL_BLOCK_SENDERS,BITGROUP_1) 
              || $priv->has_priv(ACL_EMAIL_EXPORT,BITGROUP_1)
              ) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=rules"); ?>" class="cer_configuration_leftbox_link">Parser Mail Rules</a></td>
          </tr>
					<?php } ?>
					<?php if($priv->has_priv(ACL_PARSER_LOG,BITGROUP_2)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=log"); ?>" class="cer_configuration_leftbox_link">Parser/GUI Log</a></td>
          </tr>
					<?php } ?>
						<?php if($priv->has_priv(ACL_EMAIL_BLOCK_SENDERS,BITGROUP_1)) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=addresses"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_EMAIL_BLOCK; ?></a></td>
          </tr>
								<?php } ?>
								<?php if($priv->has_priv(ACL_EMAIL_EXPORT,BITGROUP_1) && !DEMO_MODE) { ?>
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=export"); ?>" class="cer_configuration_leftbox_link"><?php echo LANG_CONFIG_MENU_EMAIL_EXPORT; ?></a></td>
          </tr>
								<?php } ?>
			<?php } ?>
			
		  <tr>
		  	<td><img src="includes/images/spacer.gif" height="5" width="1" border="0"></td>
		  </tr>
		  
		  <tr class="boxtitle_gray_glass_dk"> 
	        <td align="center" nowrap>- Support Center -</td>
	      </tr>
				
	  <?php if($priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2)) { ?>
		  <tr class="boxtitle_blue_glass"> 
			<td nowrap>
				<img src="includes/images/config/icon_support_center.gif" border="0" align="absmiddle">
				Support Center Settings
			</td>
          </tr>
      <?php if($priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2)) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=public_gui_profiles"); ?>" class="cer_configuration_leftbox_link">Public GUI Profiles</a></td>
          </tr>
	  		<?php } ?>
      <?php if($priv->has_priv(ACL_PUBLIC_GUI,BITGROUP_2)) { ?> 
          <tr class="cer_configuration_leftbox_body"> 
            <td><a href="<?php echo cer_href("configuration.php?module=public_gui_fields"); ?>" class="cer_configuration_leftbox_link">Public GUI Custom Field Groups</a></td>
          </tr>
					<?php } ?>
			<?php } ?>
				
    </table>
