/***************************************************************
*  Copyright notice
*
*  (c) 2011 Alexander Kraskov <alexander.kraskov@telekom.de>
*      Developer Garden (www.developergarden.com)
*	   Deutsche Telekom AG
*      Products & Innovation
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

confcall_header1 = "";
confcall_header2 = "";

function confcall_utils_strlen( string ) {
    return string.length;
}

function confcall_utils_addZero( string ) {
	string = string.toString();
	if (string.length == 1) {
		return '0' + string;
	} else {
		return string;
	}
}

function confcall_utils_jQuery() {
	if (typeof jQuery_new != "undefined" && typeof jQuery == "undefined") {
		jQuery = jQuery_new;
	}
	if (typeof jQuery != "undefined") {
		return true;
	} else {
		alert("Can't find jQuery");
		return false;
	}
}

function confcall_view_disabledTest() {
	if (confcall_utils_jQuery()) {
		if (typeof jQuery(".confcallbutton").attr("disabled") == "undefined") {
			return false;
		} else {
			return jQuery(".confcallbutton").attr("disabled");
		}
	} else {
		return false;
	}
}

function confcall_view_adHocList() {
 	if (confcall_utils_jQuery()) {
		if (confcall_view_loaderTest() == false) {
			if (confcall_header1.length == 0) {
				confcall_header1 = jQuery("#conf_list_header").html();
				confcall_header2 = jQuery("#conf_list_part_header").html();
			}
			jQuery("#conf_adhoc_link").css("font-weight", "bold");
			jQuery("#conf_planned_link").css("font-weight", "normal");
			jQuery("#conf_failed_link").css("font-weight", "normal");
			jQuery("#conf_not_commited_link").css("font-weight", "normal");
			confcall_core_conferenceList(1);
		}
	}
}

function confcall_view_plannedList() {
	if (confcall_utils_jQuery()) {
		if (confcall_view_loaderTest() == false) {
			if (confcall_header1.length == 0) {
				confcall_header1 = jQuery("#conf_list_header").html();
				confcall_header2 = jQuery("#conf_list_part_header").html();
			}
			jQuery("#conf_adhoc_link").css("font-weight", "normal");
			jQuery("#conf_planned_link").css("font-weight", "bold");
			jQuery("#conf_failed_link").css("font-weight", "normal");
			jQuery("#conf_not_commited_link").css("font-weight", "normal");
			confcall_core_conferenceList(2);
		}
	}
}

function confcall_view_failedList() {
	if (confcall_utils_jQuery()) {
		if (confcall_view_loaderTest() == false) {
			if (confcall_header1.length == 0) {
				confcall_header1 = jQuery("#conf_list_header").html();
				confcall_header2 = jQuery("#conf_list_part_header").html();
			}
			jQuery("#conf_adhoc_link").css("font-weight", "normal");
			jQuery("#conf_planned_link").css("font-weight", "normal");
			jQuery("#conf_failed_link").css("font-weight", "bold");
			jQuery("#conf_not_commited_link").css("font-weight", "normal");
			confcall_core_conferenceList(3);
		}
	}
}

function confcall_view_notCommitedList() {
	if (confcall_utils_jQuery()) {
		if (confcall_view_loaderTest() == false) {
			if (confcall_header1.length == 0) {
				confcall_header1 = jQuery("#conf_list_header").html();
				confcall_header2 = jQuery("#conf_list_part_header").html();
			}
			jQuery("#conf_adhoc_link").css("font-weight", "normal");
			jQuery("#conf_planned_link").css("font-weight", "normal");
			jQuery("#conf_failed_link").css("font-weight", "normal");
			jQuery("#conf_not_commited_link").css("font-weight", "bold");
			confcall_core_conferenceList(4);
		}
	} 
}

function confcall_view_loaderTest() {
	if (confcall_utils_jQuery()) {	
		if (typeof jQuery("#conf_loader").css("#display") == "undefined") {
			return false;
		} else {
			if (jQuery("#conf_loader").css("display") == "block") {
				return true;
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
}

function confcall_core_conferenceList(t) {
	if (confcall_utils_jQuery()) {
		jQuery("#confcallstep1").hide("slow");
		confcall_view_showLoader();
		jQuery("#confcallnewconfbutton").attr("disabled", "disabled");
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 5,
					t: t
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					var table = '';
					var confs = '';
					var i = 1;
					var initiator = '';
					jQuery.each(response.Confs, function(i, row) {
						table = '<table class="dgtable" id="participants_table' + i + '" style="display:none;"><thead id="conf_list_part_header">' + confcall_header2 + '</thead><tbody>';
						jQuery.each(row.participants, function(j, participant) {
							if (participant.isInitiator == "true") {
								initiator = jQuery("#conf_yes").val();
							} else {
								initiator = jQuery("#conf_no").val();
							}
							table += '<tr>';
							table += '<td>' + participant.firstName + '</td>';
							table += '<td>' +  participant.lastName + '</td>';
							table += '<td>' + participant.number + '</td>';
							table += '<td>' + participant.email + '</td>';
							table += '<td>' + initiator + '</td>';
							table += '</tr>'
						});
						table = table + '</tbody></table>';
						if (row.participants_count > 0) {
							confs = confs +
								'<tbody id="rid' + row.confID + '"><tr>' +
									'<td>' + row.name + '</td>' + 
									'<td>' + row.description + '</td>' + 
									'<td>' + row.duration + '</td>' + 
									'<td>' + row.starttime + '</td>' +
									'<td><a id="participants_count" href="#" onclick="confcall_view_showParticipants(' + i + '); return false;">' + row.participants_count + '</a></td>' +
									'<td><input type="button" onclick="confcall_core_removeConference(' + "'" + row.confID + "', " + i + ');" value="Delete" class="confcallbutton"></td>' +
									'</tr><tr><td colspan="6">' + table + '</td></tr></tbody>';
						} else {
							confs = confs +
								'<tbody id="rid' + row.confID + '"><tr>' +
									'<td>' + row.name + '</td>' + 
									'<td>' + row.description + '</td>' + 
									'<td>' + row.duration + '</td>' + 
									'<td>' + row.starttime + '</td>' +
									'<td>0</td>' +
									'<td><input type="button" onclick="confcall_core_removeConference(' +"'"+row.confID+"'" +', 0);" value="Delete" class="confcallbutton">' +
								'</td></tr></tbody>';
						}
						i++;
					});
					
					jQuery("#conf_table").html('<thead id="conf_list_header">' + confcall_header1 + '</thead>' + confs);
					jQuery("#confcallnewconfbutton").removeAttr("disabled", "");
					jQuery("#conf_loader").hide();
					jQuery("#confcallconflist").show();
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_removeConference(id, i) {
	if (confcall_utils_jQuery()) {
		// PAGE
		confcall_view_showLoader();
		jQuery(".confcallbutton").attr("disabled", "disabled");
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 6,
					confID: id
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#rid"+id).remove();
					if (i > 0) {
						jQuery("#participants_table" + i).remove();
					}
					jQuery(".confcallbutton").removeAttr("disabled");
					jQuery("#conf_loader").hide();
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
						jQuery(".confcallbutton").removeAttr("disabled");
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_removeConference_cancel_link(id, hrf) {
	if (confcall_utils_jQuery()) {
		// PAGE
		jQuery("#conf_header").hide();
		jQuery("#conf_participants").hide();
		confcall_view_showLoader();
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 6,
					confID: id
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					// ook
					window.location = hrf;
				} else {
					jQuery("#conf_header").show();
					jQuery("#conf_participants").show();
					jQuery("#conf_loader").show();
					// not ook
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_removeConferenceTemplate(id) {
	if (confcall_utils_jQuery()) {
		// PAGE
		confcall_view_showLoader();
		jQuery(".confcallbutton").attr("disabled", "disabled");
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 12,
					templateId: id
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#" + id).remove();
					jQuery(".confcallbutton").removeAttr("disabled");
					jQuery("#conf_loader").hide();
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
						jQuery(".confcallbutton").removeAttr("disabled");
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_newParticipant() {
	if (confcall_utils_jQuery()) {
		// PAGE
		var conf_initiator = "false";
		var conf_initiator_text = jQuery("#conf_no").val();
		var remove_text = jQuery("#conf_remove").val();
		if  (jQuery("#conf_initiator_input").attr('checked')) {
			conf_initiator = "true";
			conf_initiator_text = jQuery("#conf_yes").val();
		}
		confcall_view_showLoader();
		var state = jQuery("#conf_commit_button").attr("disabled");
		jQuery("#conf_commit_button").attr("disabled", "disabled");
		confcall_view_disableParticipantForm("disabled");
		var conf_com = 3;
		if (jQuery("#confcalltype").val() == "template") {
			conf_com = 11;
		}
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: conf_com,
					confID: jQuery("#conf_id").val(),
					firstname: jQuery("#conf_firstname").val(),
					lastname: jQuery("#conf_lastname").val(),
					phonenumber: jQuery("#conf_phone").val(),
					email: jQuery("#conf_email").val(),
					initiator: conf_initiator
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#conf_loader").hide();
					confcall_view_disableParticipantForm("");
					if (state != "disabled") {
						jQuery("#conf_commit_button").removeAttr("disabled");
					}
					if (conf_initiator == "true") {
						jQuery("#conf_initiator").hide("slow");
						jQuery("#conf_commit_button").removeAttr("disabled");
						jQuery("#conf_initiator_input").removeAttr("checked");
					} 
					jQuery("#conf_participants_table").html(
						jQuery("#conf_participants_table").html() +
							'<tr id="rid' + response.ID + '">' +
								'<td>' + jQuery("#conf_firstname").val() + '</td>' +
								'<td>' + jQuery("#conf_lastname").val() + '</td>' +
								'<td>' + jQuery("#conf_phone").val() + '</td>' +
								'<td>' + jQuery("#conf_email").val() + '</td>' +
								'<td>' + conf_initiator_text + '</td>' +
								'<td><input type="button" class="confcallbutton" onclick="confcall_core_removeParticipant(\'' + response.ID + '\', ' + conf_initiator + ');" value="' + remove_text + '" /></td></tr>');
					// Reset input fields
					jQuery("#conf_firstname").val("");
					jQuery("#conf_lastname").val("");
					jQuery("#conf_phone").val("");
					jQuery("#conf_email").val("");
				} else {
					if (response.Status == "Error") {
						jQuery("#conf_loader").hide();
						confcall_view_showError(response.Message);
						confcall_view_disableParticipantForm("");
						jQuery("#conf_commit_button").attr("disabled", state);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_newParticipantInTemplate() {
	if (confcall_utils_jQuery()) {
		// PAGE
		confcall_view_showLoader();
		confcall_view_disableParticipantInTemplateForm("disabled");
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 11,
					confID: jQuery("#conf_temp_id").val(),
					firstname: jQuery("#conf_temp_firstname").val(),
					lastname: jQuery("#conf_temp_lastname").val(),
					phonenumber: jQuery("#conf_temp_phone").val(),
					email: jQuery("#conf_temp_email").val()
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#conf_loader").hide();
					confcall_view_disableParticipantInTemplateForm("");
					jQuery("#conf_temp_participants_table").html(jQuery("#conf_temp_participants_table").html() +
						'<tr id="rid' + response.ID + '">' +
						'<td>' + jQuery("#conf_temp_firstname").val() + '</td>' +
						'<td>' + jQuery("#conf_temp_lastname").val() + '</td>' +
						'<td>' + jQuery("#conf_temp_phone").val() + '</td>' +
						'<td>' + jQuery("#conf_temp_email").val() + '</td>' +
						'<td>' + jQuery("#conf_no").val() + '</td></tr>');
					// Reset input fields
					jQuery("#conf_temp_firstname").val("");
					jQuery("#conf_temp_lastname").val("");
					jQuery("#conf_temp_phone").val("");
					jQuery("#conf_temp_email").val("");
				} else {
					if (response.Status == "Error") {
						jQuery("#conf_loader").hide();
						confcall_view_showError(response.Message);
						confcall_view_disableParticipantInTemplateForm("");
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_removeParticipant(id, initiator) {
	if (confcall_utils_jQuery()) {
		// PAGE
		confcall_view_showLoader();
		var state = jQuery("#conf_commit_button").attr("disabled");
		jQuery("#conf_commit_button").attr("disabled", "disabled");
		confcall_view_disableParticipantForm("disabled");
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 4,
					confID: jQuery("#conf_id").val(),
					partID: id
				  },
				
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#conf_loader").hide();
					confcall_view_disableParticipantForm("");
					jQuery("#conf_commit_button").attr("disabled", state);
					jQuery("#rid"+id).remove();
					if (initiator == true) {
						jQuery("#conf_initiator").show("slow");
						jQuery("#conf_commit_button").attr("disabled", "disabled");
					}
				} else {
					if (response.Status == "Error") {
						jQuery("#conf_loader").hide();
						confcall_view_showError(response.Message);
						confcall_view_disableParticipantForm("");
						jQuery("#conf_commit_button").attr("disabled", state);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_removeParticipant_running_conf(id) {
	if (confcall_utils_jQuery()) {
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 4,
					confID: jQuery("#conf_id").val(),
					partID: id
				  },
				
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#" + id).remove();
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_view_disableParticipantForm(disabled) {
	if (confcall_utils_jQuery()) {
		if (disabled == "disabled") {
			jQuery("#conf_firstname").attr("disabled", disabled);
			jQuery("#conf_lastname").attr("disabled", disabled);
			jQuery("#conf_phone").attr("disabled", disabled);
			jQuery("#conf_email").attr("disabled", disabled);
			jQuery("#conf_initiator_input").attr("disabled", disabled);
			jQuery("#conf_add_participant_button").attr("disabled", disabled);
			jQuery(".confcallbutton").attr("disabled", disabled);
		} else {
			jQuery("#conf_firstname").removeAttr("disabled");
			jQuery("#conf_lastname").removeAttr("disabled");
			jQuery("#conf_phone").removeAttr("disabled");
			jQuery("#conf_email").removeAttr("disabled");
			jQuery("#conf_initiator_input").removeAttr("disabled");
			jQuery("#conf_add_participant_button").removeAttr("disabled");
			jQuery(".confcallbutton").removeAttr("disabled");
		}
	}
}

function confcall_view_disableParticipantInTemplateForm(disabled) {
	if (confcall_utils_jQuery()) {
		if (disabled == "disabled") {
			jQuery("#conf_temp_firstname").attr("disabled", disabled);
			jQuery("#conf_temp_lastname").attr("disabled", disabled);
			jQuery("#conf_temp_phone").attr("disabled", disabled);
			jQuery("#conf_temp_email").attr("disabled", disabled);
			jQuery("#conf_temp_add_participant_button").attr("disabled", disabled);
			jQuery(".confcallbutton").attr("disabled", disabled);
		} else {
			jQuery("#conf_temp_firstname").removeAttr("disabled");
			jQuery("#conf_temp_lastname").removeAttr("disabled");
			jQuery("#conf_temp_phone").removeAttr("disabled");
			jQuery("#conf_temp_email").removeAttr("disabled");
			jQuery("#conf_temp_add_participant_button").removeAttr("disabled");
			jQuery(".confcallbutton").removeAttr("disabled");
		}
	}
}

function confcall_core_commitConference() {
	if (confcall_utils_jQuery()) {
		// PAGE
		jQuery("#conf_header").hide();
		jQuery("#conf_participants").hide();
		confcall_view_showLoader();
		// AJAX
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 0,
					confID: jQuery("#conf_id").val()
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					if (jQuery("#conf_type").val() == "adhoc") {
						confcall_core_getRunningConferenceStatus(jQuery("#conf_id").val(), false, null);
					} else {
						jQuery("#conf_loader").hide();
						jQuery("#conf_end_text").html('Conferece has been commited successfully');
						jQuery("#conf_end_div").show();
						jQuery("#conf_end").show();
					}
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_core_getRunningConferenceStatus(id, r, t) {
	if (confcall_utils_jQuery()) {
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 8,
					confID: id
                  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					if (response.Conference.conference_end > 0) {
						t = new Date(response.Conference.conference_end);
						jQuery(".confcallbutton").attr("disabled", "disabled");
					}
					var s = 0;
					var m = 0;
					var duration = response.Conference.duration;
					// Timer
					if (t == null) {
						jQuery("#conf_duration_m").html("00");
						jQuery("#conf_duration_s").html("00");
					} else {
						s = Math.round(duration - ((new Date()).valueOf() - t.valueOf()) / 1000);
						m = Math.floor(s / 60);
						if (s < 0) {
							jQuery("#conf_duration_m").html("00");
							jQuery("#conf_duration_s").html("00");
						} else {
							jQuery("#conf_duration_m").html(confcall_utils_addZero(m));
							jQuery("#conf_duration_s").html(confcall_utils_addZero(s - 60 * m));
						}
					}
					// Table
					var rows = "";
					var initiator = "";
					var muted = "";
					if (response.Conference.participants_count > 0) {
					jQuery.each(response.Conference.participants, function(i, participant) {
							if (participant.isInitiator == "true") {
								initiator = jQuery("#conf_yes").val();
							} else {
								initiator = jQuery("#conf_no").val();
							}
							if (participant.muted == "true") {
								muted = jQuery("#conf_yes").val();
							} else {
								muted = jQuery("#conf_no").val();
							}
							rows += '<tr id="' + participant.ID + '">';
							rows += '<td>' + participant.firstName +'<br/>';
							rows += participant.lastName + '<br/>';
							rows += participant.email + '</td>';
							rows += '<td>' + participant.number + '<br/>';
							rows += initiator + '</td>';
							rows += '<td>' + participant.status + '</td>';
							rows += '<td>' + muted + '</td>';
							rows += '<td><input type="button" value="Mute" onclick="confcall_mute(' + "'" + participant.ID + "'" + ');" class="confcallbutton"><br/>';
							rows += '<input type="button" value="UnMute" onclick="confcall_unmute(' + "'" + participant.ID + "'" + ');" class="confcallbutton"><br/>';
							rows += '<input type="button" value="Redial" onclick="confcall_redial(' + "'" + participant.ID + "'" + ');" class="confcallbutton"><br/>';
							rows += '<input type="button" value="' + jQuery("#conf_remove").val() + '" onclick="confcall_core_removeParticipant_running_conf(' + "'" + participant.ID + "'" + ');" class="confcallbutton"></td>';
							rows += '</tr>'
						});
					}
					jQuery("#conf_running_table").html(rows);
					if (r == false) {
						jQuery("#conf_loader").hide();
						//jQuery("#conf_header").show();
						jQuery("#conf_running").show();
					}
					if (t == null) {
						if (response.Conference.conference_begin.length > 0) {
							t = new Date(response.Conference.conference_begin);
						}
					}
					//Recursion :
					if (response.Conference.conference_end.length > 0) {
						jQuery("#conf_running").hide();
						jQuery("#conf_end_text").html('The conference was ended.');
						jQuery("#conf_end_div").show();
						jQuery("#conf_end").show();
					} else {
						confcall_core_getRunningConferenceStatus(id, true, t);
					}
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
						jQuery("#conf_running").hide();
						jQuery("#conf_end").show();
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_view_newConference() {
 	if (confcall_utils_jQuery()) {
		var date = new Date();
		jQuery("#confcallhour").val(date.getHours());
		jQuery("#confcallminute").val(date.getMinutes());
		jQuery("#confcalldayOfMonth").val(date.getDate());
		jQuery("#confcallmonth").val(date.getMonth());
		jQuery("#confcallyear").val(date.getFullYear());
		
		jQuery("#confcallfirstname").val("");
		jQuery("#confcalllastname").val("");
		jQuery("#confcallphonenumber").val("");
		jQuery("#confcallemail").val("");
		jQuery("#conf_initiator").show();
		jQuery("#conf_initiatorinput").removeAttr('checked');
		jQuery("#conf_participants_table").html('');
		jQuery("#confcallrunningconftable").html('<thead><tr><td>Name</td><td>Phone number</td><td>E-mail</td><td>Initiator</td><td>Status</td><td>Muted</td></tr></thead>');
		jQuery("#conf_end").hide();
		jQuery("#conf_error_message").hide();
		jQuery("#confcallstep1").show();
	}
}

function confcall_core_newTemplate() {
	if (confcall_utils_jQuery()) {
		var join_confirmation = "false";
		if  (jQuery("#temp_join_input").attr('checked')) {
			join_confirmation = "true";
		}
		jQuery("#conf_templates").hide();
		confcall_view_showLoader();
		jQuery.ajax({
			url: "index.php",
			type: "POST",
			data: {
					eID: "conferencecall",
					command: 10,
					name: jQuery("#temp_name_input").val(),
					description: jQuery("#temp_desc_input").val(),
					duration: jQuery("#temp_dur_input").val(),
					firstName: jQuery("#temp_init_firstname_input").val(),
					lastName: jQuery("#temp_init_lastname_input").val(),
					phone: jQuery("#temp_init_phone_input").val(),
					email: jQuery("#temp_init_email_input").val(),
					joinConfirm: join_confirmation
				  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#conf_loader").hide();
					jQuery("#conf_templates").hide();
					jQuery("#conf_participants_in_temp").show();
					jQuery("#conf_temp_id").val(response.TemplateId);
					jQuery("#conf_temp_name").html(jQuery("#temp_name_input").val());
					jQuery("#conf_temp_description").html(jQuery("#temp_desc_input").val());
					jQuery("#conf_temp_duration").html(jQuery("#temp_dur_input").val());
					jQuery("#conf_temp_participants_table").html(
						'<tr><td>' + jQuery("#temp_init_firstname_input").val() + '</td>' +
						'<td>' + jQuery("#temp_init_lastname_input").val() + '</td>' +
						'<td>' + jQuery("#temp_init_phone_input").val() + '</td>' +
						'<td>' + jQuery("#temp_init_email_input").val() + '</td>' +
						'<td>' + jQuery("#conf_yes").val() + '</td></tr>');
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
						jQuery("#conf_templates").show();
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_view_showTemplateForm() {
	if (confcall_utils_jQuery()) {
		jQuery("#conf_create_new_template").hide();
		jQuery("#conf_cancel_new_template").show();
		jQuery("#conf_new_template").show("fast");
	}
}

function confcall_view_hideTemplateForm() {
	if (confcall_utils_jQuery()) {
		jQuery("#conf_create_new_template").show();
		jQuery("#conf_cancel_new_template").hide();
		jQuery("#conf_new_template").hide("fast");
	}
}

function confcall_core_updateParticipant(id, action) {
	if (confcall_utils_jQuery()) {
		jQuery.ajax({
		url: "index.php",
		type: "POST",
			data: {
					eID: "conferencecall",
					command: 9,
					action: action,
					confID: jQuery("#conf_id").val(),
					partID: id
                  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					// update ok
				} else {
					if (response.Status == "Error") {
						confcall_view_showError(response.Message);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}

function confcall_view_showLoader() {
	jQuery("#conf_error_message").hide();
	jQuery("#conf_loader").show();
}

function confcall_view_showError(text) {
	jQuery("#conf_error_text").html(text)
	jQuery("#conf_loader").hide();
	jQuery("#conf_error_message").show();
}

function confcall_view_showAJAXError(error) {
	alert("Sorry, hat nicht funktioniert");
}

function confcall_mute(id) {
 	confcall_core_updateParticipant(id, 1);
	return false;
}

function confcall_unmute(id) {
 	confcall_core_updateParticipant(id, 0);
	return false;
}

function confcall_redial(id) {
 	confcall_core_updateParticipant(id, 2);
	return false;
}

function confcall_view_plannedConference() {
	if (confcall_utils_jQuery()) {
		if  (jQuery("#conf_planned_input").attr('checked')) {
			jQuery("#conf_new_datetime").show("slow");
		} else {
			jQuery("#conf_new_datetime").hide("slow");
		}
	}
}

function confcall_view_showParticipants(i) {
	if (confcall_utils_jQuery()) {
		if (confcall_view_disabledTest() == false) {
			jQuery("#participants_table" + i).show("slow");
		}
	}
}

function confcall_core_runTemplate(id) {
	if (confcall_utils_jQuery()) {
		// PAGE
		confcall_view_showLoader();
		jQuery("#conf_templates").hide();
		// AJAX
		jQuery.ajax({
		url: "index.php",
		type: "POST",
			data: {
					eID: "conferencecall",
					command: 13,
					tempID: id
                  },
			dataType: "json",
			success: function(response) {
				if (response.Status == "Ok") {
					jQuery("#conf_loader").hide();
					jQuery("#conf_running").show();
					jQuery("#conf_id").val(response.ConfID);
					confcall_core_getRunningConferenceStatus(response.ConfID, false, null);
				} else {
					if (response.Status == "Error") {
						jQuery("#conf_running").show();
						confcall_view_showError(response.Message);
					}
				}
			},
			error: function(error) {
				confcall_view_showAJAXError(error);
			}
		});
	}
}