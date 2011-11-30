#
# Table structure for table "tx_conferencecall_calls"
#
CREATE TABLE tx_conferencecall_calls (
  fe_user_id int(11) unsigned NOT NULL,
  conferences_created int(11) unsigned DEFAULT '0' NOT NULL,
  conferences_created_in_period int(11) unsigned DEFAULT '0' NOT NULL,
  period_start datetime NOT NULL,
  period_end datetime NOT NULL,
  PRIMARY KEY (fe_user_id)
);
#
# Table structure for table "tx_conferencecall_statistics"
#
CREATE TABLE tx_conferencecall_statistics (
  confcall_id INT NOT NULL AUTO_INCREMENT,
  confcall_day int(11) unsigned NOT NULL,
  confcall_month int(11) unsigned NOT NULL,
  confcall_year int(11) unsigned NOT NULL,
  confcall_hour int(11) unsigned NOT NULL,
  PRIMARY KEY (confcall_id)
);