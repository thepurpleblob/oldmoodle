

-- 
-- Table structure for table `prefix_question_shortanswer`
-- 

CREATE TABLE prefix_question_shortanswer (
  id int(10) unsigned NOT NULL auto_increment,
  question int(10) unsigned NOT NULL default '0',
  answers varchar(255) NOT NULL default '',
  usecase tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY question (question)
) ENGINE=MyISAM COMMENT='Options for short answer questions';

-- --------------------------------------------------------