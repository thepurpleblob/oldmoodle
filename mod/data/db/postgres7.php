<?php

function data_upgrade($oldversion) {
/// This function does anything necessary to upgrade
/// older versions to match current functionality

    global $CFG;

    if ($oldversion < 2006011900) {
        table_column("data_content", "", "content1", "text", "", "", "", "not null");
        table_column("data_content", "", "content2", "text", "", "", "", "not null");
        table_column("data_content", "", "content3", "text", "", "", "", "not null");
        table_column("data_content", "", "content4", "text", "", "", "", "not null");
    }

    if ($oldversion < 2006011901) {
        table_column("data", "", "approval", "integer", "4", "unsigned", "0", "not null");
        table_column("data_records", "", "approved", "integer", "4", "unsigned", "0", "not null");
    }
    
    if ($oldversion < 2006020801) {
        table_column("data", "", "scale", "integer");
        table_column("data", "", "assessed", "integer");
        table_column("data", "", "assesspublic", "integer");
    }

    if ($oldversion < 2006022700) {
        table_column("data_comments", "", "created", "integer");
        table_column("data_comments", "", "modified", "integer");
    }
    
    if ($oldversion < 2006030700) {
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'view', 'data', 'name');");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'add', 'data', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'update', 'data', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'record delete', 'data', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'fields add', 'data_fields', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'fields update', 'data_fields', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'templates saved', 'data', 'name')");
        modify_database('', "INSERT INTO prefix_log_display (module, action, mtable, field) VALUES ('data', 'templates defaults', 'data', 'name')");
    }
    
    if ($oldversion < 2006032700) {
        table_column('data', '', 'defaultsort', 'integer', '10', 'unsigned', '0');
        table_column('data', '', 'defaultsortdir', 'tinyint', '4', 'unsigned', '0', 'not null', 'defaultsort');
        table_column('data', '', 'editany', 'tinyint', '4', 'unsigned', '0', 'not null', 'defaultsortdir');
    }

    if ($oldversion < 2006032900) {
        table_column('data', '', 'csstemplate', 'text', '', '', '', 'not null', 'rsstemplate');
    }
    
    if ($oldversion < 2006050500) { // drop all tables, and create from scratch

        execute_sql("DROP TABLE {$CFG->prefix}data", false);
        execute_sql("DROP TABLE {$CFG->prefix}data_content", false);
        execute_sql("DROP TABLE {$CFG->prefix}data_fields", false);
        execute_sql("DROP TABLE {$CFG->prefix}data_records", false);
        execute_sql("DROP TABLE {$CFG->prefix}data_comments", false);
        execute_sql("DROP TABLE {$CFG->prefix}data_ratings", false);

        modify_database('',"CREATE TABLE prefix_data (
                              id SERIAL PRIMARY KEY,
                              course integer NOT NULL default '0',
                              name varchar(255) NOT NULL default '',
                              intro text NOT NULL default '',
                              ratings integer NOT NULL default '0',
                              comments integer NOT NULL default '0',
                              timeavailablefrom integer NOT NULL default '0',
                              timeavailableto integer NOT NULL default '0',
                              timeviewfrom integer NOT NULL default '0',
                              timeviewto integer NOT NULL default '0',
                              participants integer NOT NULL default '0',
                              requiredentries integer NOT NULL default '0',
                              requiredentriestoview integer NOT NULL default '0',
                              maxentries integer NOT NULL default '0',
                              rssarticles integer NOT NULL default '0',
                              singletemplate text NOT NULL default '',
                              listtemplate text NOT NULL default '',
                              listtemplateheader text NOT NULL default '',
                              listtemplatefooter text NOT NULL default '',
                              addtemplate text NOT NULL default '',
                              rsstemplate text NOT NULL default '',
                              csstemplate text NOT NULL default '',
                              approval integer NOT NULL default '0',
                              scale integer NOT NULL default '0',
                              assessed integer NOT NULL default '0',
                              assesspublic integer NOT NULL default '0',
                              defaultsort integer NOT NULL default '0',
                              defaultsortdir integer NOT NULL default '0',
                              editany integer NOT NULL default '0'
                            );

                            CREATE TABLE prefix_data_content (
                              id SERIAL PRIMARY KEY,
                              fieldid integer NOT NULL default '0',
                              recordid integer NOT NULL default '0',
                              content text NOT NULL default '',
                              content1 text NOT NULL default '',
                              content2 text NOT NULL default '',
                              content3 text NOT NULL default '',
                              content4 text NOT NULL default ''
                            );

                            CREATE TABLE prefix_data_fields (
                              id SERIAL PRIMARY KEY,
                              dataid integer NOT NULL default '0',
                              type varchar(255) NOT NULL default '',
                              name varchar(255) NOT NULL default '',
                              description text NOT NULL default '',
                              param1  text NOT NULL default '',
                              param2  text NOT NULL default '',
                              param3  text NOT NULL default '',
                              param4  text NOT NULL default '',
                              param5  text NOT NULL default '',
                              param6  text NOT NULL default '',
                              param7  text NOT NULL default '',
                              param8  text NOT NULL default '',
                              param9  text NOT NULL default '',
                              param10 text NOT NULL default ''
                            );

                            CREATE TABLE prefix_data_records (
                              id SERIAL PRIMARY KEY,
                              userid integer NOT NULL default '0',
                              groupid integer NOT NULL default '0',
                              dataid integer NOT NULL default '0',
                              timecreated integer NOT NULL default '0',
                              timemodified integer NOT NULL default '0',
                              approved integer NOT NULL default '0'
                            );

                            CREATE TABLE prefix_data_comments (
                              id SERIAL PRIMARY KEY,
                              userid integer NOT NULL default '0',
                              recordid integer NOT NULL default '0',
                              content text NOT NULL default '',
                              created integer NOT NULL default '0',
                              modified integer NOT NULL default '0'
                            );

                            CREATE TABLE prefix_data_ratings (
                              id SERIAL PRIMARY KEY,
                              userid integer NOT NULL default '0',
                              recordid integer NOT NULL default '0',
                              rating integer NOT NULL default '0'
                            );");
                            
    }
    
    if ($oldversion < 2006052400) {
        table_column('data','','rsstitletemplate','text','','','','not null','rsstemplate');
    }
    
    return true;
}

?>
