laboratree.inbox = {};
laboratree.inbox.masks = {};
laboratree.inbox.dashboard = {};
laboratree.inbox.view = {};
laboratree.inbox.neighbors = {};
laboratree.inbox.contacts = {};
laboratree.inbox.send = {};
laboratree.inbox.addressbook = {};

/**
 * Dashboard View
 */
laboratree.inbox.makeDashboard = function(div, data_url, title) {
	laboratree.inbox.dashboard = new laboratree.inbox.Dashboard(div, data_url, title);
};

laboratree.inbox.Dashboard = function(div, data_url, title) {
	Ext.QuickTips.init();

	this.store = new Ext.data.JsonStore({
		root: 'messages',
		autoLoad: true,
		idProperty: 'id',
		url: data_url,
		remoteSort: true,
		fields: [
			'from', 'to', 'subject', {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}, 'status', 'body', 'trash', 'attachments', 'group', 'project'
		]
	});	

	this.store.setDefaultSort('date', 'DESC');

	this.sm = new Ext.grid.CheckboxSelectionModel();
	this.grid = new Ext.grid.GridPanel({
		id: 'dashboard',
		title: title,
		renderTo: div,
		width: '100%',
		height: 600,

		store: this.store,
		loadMask: true,

		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [
			this.sm,{
				id: 'attachments',
				header: '<img src="/img/icons/attachment_mail.gif" width="12" height="12" alt="A" />',
				dataIndex: 'attachments',
				width: 15,
				renderer: laboratree.inbox.render.dashboard.attachments
			},{
				id: 'from',
				header: 'From',
				dataIndex: 'from',
				width: 115,
				renderer: laboratree.inbox.render.dashboard.from
			},{
				id: 'to',
				header: 'To',
				dataIndex: 'to',
				width: 115
			},{
				id: 'subject',
				header: 'Subject',
				dataIndex: 'subject',
				width: 290,
				renderer: laboratree.inbox.render.dashboard.subject
			},{
				id: 'date',
				header: 'Date',
				dataIndex: 'date',
				width: 150,
				renderer: Ext.util.Format.dateRenderer('m/d/Y g:i a')
			}]
		}),
		sm: this.sm,

		viewConfig: {
			forceFit: true,
			getRowClass: function(record, index) {
				return 'x-grid3-row-' + record.get('status');
			}
		},

		tbar: [{
			text: 'Received',
			handler: function() {
				window.location = laboratree.links.inbox.received;
			}
		},{
			xtype: 'tbseparator'
		},{

			text: 'Sent',
			handler: function() {
				window.location = laboratree.links.inbox.sent;
			}
		},{
			xtype: 'tbseparator'
		},{

			text: 'Trash',
			handler: function() {
				window.location = laboratree.links.inbox.trash;
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'New Message',
			handler: function() {
				window.location = String.format(laboratree.links.inbox.send, '', '');
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'Actions',
			menu: {	
				id: 'action-menu',
				xtype: 'menu',
				cls: 'inbox-menu',
				items: [{
					id: 'read',
					text: 'Mark as Read',
					handler: function(item, e) {
						laboratree.inbox.dashboard.sm.each(function(record) {
							Ext.Ajax.request({
								url: String.format(laboratree.links.inbox.read, record.id) + '.json',
								success: function(response, request) {
									var data = Ext.decode(response.responseText);

									if(data.error) {
										request.failure();
										return;
									}

									record.set('status', 'read');
									laboratree.inbox.dashboard.sm.clearSelections();
								},
								failure: function(response, request) {
									laboratree.inbox.dashboard.sm.clearSelections();
								}
							});
						});
					}
				},{
					id: 'unread',
					text: 'Mark as Unread',
					handler: function(item, e) {
						laboratree.inbox.dashboard.sm.each(function(record) {
							Ext.Ajax.request({
								url: String.format(laboratree.links.inbox.unread, record.id) + '.json',
								success: function(response, request) {
									var data = Ext.decode(response.responseText);

									if(data.error) {
										request.failure();
										return;
									}

									record.set('status', 'unread');
									laboratree.inbox.dashboard.sm.clearSelections();
								},
								failure: function(response, request) {
									laboratree.inbox.dashboard.sm.clearSelections();
								}
							});
						});
					}
				},{
					id: 'delete',
					text: 'Delete',
					handler: function(item, e) {
						if(window.confirm('Are you sure?')) {
							laboratree.inbox.dashboard.sm.each(function(record) {
								Ext.Ajax.request({
									url: String.format(laboratree.links.inbox['delete'], record.id) + '.json',
									success: function(response, request) {
										var data = Ext.decode(response.responseText);
	
										if(data.error) {
											request.failure();
											return;
										}
	
										if(record.store) {
											record.store.load();
										}
										laboratree.inbox.dashboard.sm.clearSelections();
									},
									failure: function(response, request) {
										laboratree.inbox.dashboard.sm.clearSelections();
									}
								});
							});
						}
					}
				}]
			}
		}],
		bbar: new Ext.PagingToolbar({
			pageSize: 23,
			store: this.store,
			displayInfo: true,
			displayMsg: 'Displaying message {0} - {1} of {2}',
			emptyMsg: 'No messages to display'
		})
	});
};

laboratree.inbox.Dashboard.prototype.addRestore = function() {
	var restore = new Ext.menu.Item({
		id: 'restore',
		text: 'Restore',
		handler: function(item, e) {
			laboratree.inbox.dashboard.sm.each(function(record) {
				Ext.Ajax.request({
				url: String.format(laboratree.links.inbox.restore, record.id) + '.json',
					success: function(response, request) {
						var data = Ext.decode(response.responseText);

						if(data.error) {
							request.failure(response, request);
							return;
						}

						record.store.load();
					},
					failure: function(response, request) {

					}
				});
			});
		}
	});

	var menu = Ext.getCmp('action-menu');
	if(menu) {
		menu.add(restore);
	}
};

laboratree.inbox.makeView = function(div, data_url, inbox_id) {
	laboratree.inbox.view = new laboratree.inbox.View(div, data_url, inbox_id);

	Ext.Ajax.request({
		url: data_url,
		success: function(response, request) {
			var data = Ext.decode(response.responseText);
			if(!data) {
				request.failure(response, request);
				return;
			}

			if(!data.success) {
				request.failure(response, request);
				return;
			}

			laboratree.inbox.view.neighbors = data.neighbors;

			if(data.neighbors.prev) {
				Ext.getCmp('message-prev').setDisabled(false);
			}

			if(data.neighbors.next) {
				Ext.getCmp('message-next').setDisabled(false);
			}

			laboratree.inbox.view.message = data.message;

			var field;
			for(field in data.message) {
				if(data.message.hasOwnProperty(field)) {
					var value = data.message.field;
					if(typeof value == 'function') {
						continue;
					}

					if(laboratree.inbox.render.view.field) {
						value = laboratree.inbox.render.view.field(value, data.message);
					}

					var cmp = Ext.getCmp(field);
					if(cmp) {
						if(cmp.setValue) {
							cmp.setValue(value);
						} else if(cmp.add) {
							cmp.add(value);
							cmp.doLayout();
						}
					}
				}
			}
		},
		failure: function(response, request) {

		}
	});
};

laboratree.inbox.View = function(div, data_url, inbox_id) {
	Ext.QuickTips.init();

	this.url = data_url;
	this.inbox_id = inbox_id;
	this.message = null;

	this.form = new Ext.form.FormPanel({
		defaults: {
			xtype: 'displayfield'
		},
	
		items: [{
			id: 'subject',
			fieldLabel: 'Subject'
		},{
			id: 'from',
			fieldLabel: 'From'
		},{
			id: 'to',
			fieldLabel: 'To'
		},{
			id: 'date',
			fieldLabel: 'Date'
		}]
	});

	this.view = new Ext.Panel({
		id: 'view',
		title: 'View Message',
		renderTo: div,
		width: '100%',
		frame: true,

		layout: 'anchor',

		defaults: {
			anchor: '100%'
		},

		items: [this.form, {
			id: 'attachments',
			title: 'Attachments'
		},{
			id: 'body'
		}],
	
		tbar: ({
			style:'border: 1px #ccc solid;',
		items: [{
			text: 'Received',
			handler: function() {
				window.location = laboratree.links.inbox.received;
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'Sent',
			handler: function() {
				window.location = laboratree.links.inbox.sent;
			}
		},{
			xtype: 'tbseparator'
		},{

			text: 'Trash',
			handler: function() {
				window.location = laboratree.links.inbox.trash;
			}
		},{
			xtype: 'tbseparator'
		},{

			text: 'New Message',
			handler: function() {
				window.location = String.format(laboratree.links.inbox.send, '', '');
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'Actions',
			menu: {	
				xtype: 'menu',
				cls: 'inbox-menu',

				items: [{
					id: 'reply-to-user',
					text: 'Reply to User',
					handler: function(item, e) {
						window.location = String.format(laboratree.links.inbox.send, laboratree.inbox.view.inbox_id, '');
					}
				},{
					id: 'reply-to-group',
					text: 'Reply to Group',
					handler: function(item, e) {
						window.location = String.format(laboratree.links.inbox.send, laboratree.inbox.view.inbox_id, 'parent');
					}
				},{
					id: 'reply-to-project',
					text: 'Reply to Project',
					handler: function(item, e) {
						window.location = String.format(laboratree.links.inbox.send, laboratree.inbox.view.inbox_id, 'parent');
					}
				},{
					id: 'read',
					text: 'Mark as Read',
					handler: function(item, e) {
						Ext.Ajax.request({
							url: String.format(laboratree.links.inbox.read, laboratree.inbox.view.inbox_id) + '.json',
							success: function(response, request) {
								var data = Ext.decode(response.responseText);

								if(data.error) {
									request.failure();
									return;
								}

								laboratree.inbox.view.message.status = 'read';
							},
							failure: function(response, request) {

							}
						});
					}
				},{
					id: 'unread',
					text: 'Mark as Unread',
					hidden: true,
					handler: function(item, e) {
						Ext.Ajax.request({
							url: String.format(laboratree.links.inbox.unread, laboratree.inbox.view.inbox_id) + '.json',
							success: function(response, request) {
								var data = Ext.decode(response.responseText);

								if(data.error) {
									request.failure();
									return;
								}

								laboratree.inbox.view.message.status = 'unread';
							},
							failure: function(response, request) {

							}
						});
					}
				},{
					id: 'delete',
					text: 'Delete',
					handler: function(item, e) {
						if(window.confirm('Are you sure?')) {
							Ext.Ajax.request({
								url: String.format(laboratree.links.inbox['delete'], laboratree.inbox.view.inbox_id) + '.json',
								success: function(response, request) {
									var data = Ext.decode(response.responseText);
	
									if(data.error) {
										request.failure();
										return;
									}
	
									window.location = laboratree.links.inbox.received;
								},
								failure: function(response, request) {
	
								}
							});
						}
					}
				},{
					id: 'restore',
					text: 'Restore',
					handler: function(item, e) {
						Ext.Ajax.request({
							url: String.format(laboratree.links.inbox.restore, laboratree.inbox.view.inbox_id) + '.json',
							success: function(response, request) {
								var data = Ext.decode(response.responseText);

								if(data.error) {
									request.failure();
									return;
								}

								laboratree.inbox.view.message.trash = '0';
							},
							failure: function(response, request) {

							}
						});
					}
				}],
				listeners: {
					beforeshow: function(menu) {
						menu.items.each(function(item, index, length) {
							item.hide();
							return true;
						}, this);

						menu.items.get('reply-to-user').show();
						menu.items.get('delete').show();

						if(laboratree.inbox.view.message.group) {
							menu.items.get('reply-to-group').show();
						} else if(laboratree.inbox.view.message.project) {
							menu.items.get('reply-to-project').show();
						}

						if(laboratree.inbox.view.message.status == 'read') {
							menu.items.get('unread').show();
						} else if(laboratree.inbox.view.message.status == 'unread') {
							menu.items.get('read').show();
						}

						if(laboratree.inbox.view.message.trash == '1') {
							menu.items.get('restore').show();
						}
					}
				}
			}
		},'->',{
			id: 'message-prev',
			text: '&laquo; Previous',
			disabled: true,
			handler: function() {
				laboratree.inbox.view.navigate('prev');
			}
		},{
			xtype: 'tbseparator'
		},{
			id: 'message-next',
			text: 'Next &raquo;',
			disabled: true,
			handler: function() {
				laboratree.inbox.view.navigate('next');
			}
		}]
	})
	});
};

laboratree.inbox.View.prototype.navigate = function(direction) {
	if(this.neighbors[direction]) {
		window.location = String.format(laboratree.links.inbox.view, this.neighbors[direction]);
	}
};

/**
 * Inbox Send View
 */
laboratree.inbox.makeSend = function(div, data_url, inbox_id, rParent) {
	laboratree.inbox.reply_id = inbox_id;
	laboratree.inbox.rParent = rParent;

	laboratree.inbox.contacts = new Ext.data.JsonStore({
		root: 'contacts',
		idProperty: 'token',
		url: data_url,
		baseParams: {
			limit: 1000000
		},
		fields: [
			'id', 'name', 'token', 'type', 'image'
		]
	});

	laboratree.inbox.contacts.setDefaultSort('name');

	laboratree.inbox.send = new laboratree.inbox.Send(div, data_url);
	laboratree.inbox.addressbook = new laboratree.inbox.AddressBook(data_url);

	laboratree.inbox.contacts.load({
		callback: function(records, options, success) {
			if(laboratree.inbox.reply_id) {
				Ext.Ajax.request({
					url: String.format(laboratree.links.inbox.data, laboratree.inbox.reply_id) + '.json',
					success: function(response) {
						var inbox = Ext.decode(response.responseText);
						if(inbox) {
							var token = inbox.sessions.from;
							if(laboratree.inbox.rParent && inbox.sessions.parent) {
								token = inbox.sessions.parent;
							} 

							var record = laboratree.inbox.contacts.getById(token);
							if(record) {
								Ext.getCmp('to').addLink(record);
								Ext.getCmp('subject').setValue('Re: ' + inbox.subject);
								Ext.getCmp('message').setValue("\n\n---\n" + inbox.body);
							}
						}
					},
					scope: this
				});
			}
		}
	});
};

laboratree.inbox.Send = function(div, data_url) {
	Ext.QuickTips.init();

	this.url = data_url;

	this.store = new Ext.data.JsonStore({
		root: 'contacts',
		idProperty: 'token',
		url: data_url,
		baseParams: {
			limit: 1000000
		},
		fields: [
			'id', 'name', 'token', 'type', 'image'
		]
	});

	this.store.setDefaultSort('name');

	this.tpl = new Ext.XTemplate(
		'<tpl for=".">',
		'<table class="contactlist-item">',
			'<tr>',
				'<td class="contactlist-item-image"><img src="{image}" alt="" /></td>',
				'<td>',
					'<span class="contactlist-item-name">{name}</span>',
				'</td>',
			'</tr>',
		'</table>',
		'</tpl>'
	);

	this.combo = new laboratree.inbox.ContactBox({
		id: 'to',
		store: this.store,
		tpl: this.tpl,
		cls: 'inbox-contactbox-field',
		ctCls: 'inbox-contactbox',
		itemSelector: 'table.contactlist-item',
		hideTrigger: true,
		displayField: 'name',
		hiddenField: 'data[Inbox][hidden]',
		valueField: 'token',
		fieldLabel: 'To',
		typeAhead: false,
		minChars: 2,
		emptyText: 'Enter User name, Group name, or Project name...',
		listeners: {
			focus: function(cmp){
				cmp.removeClass('field-invalid');
			}
		}
	});

	this.form = new Ext.form.FormPanel({
		id: 'send',
		title: 'Send New Message',
		renderTo: div,
		fileUpload: true,
		width: '100%',
		height: 600,
		buttonAlign: 'center',
		labelWidth: 75,
		labelSeperator: ':',
		bodyStyle: 'padding: 5px; border-top: 0; border-left: 1px solid #6f6f6f; border-right: 1px solid #6f6f6f; border-bottom: 1px solid #6f6f6f;',
		defaultType: 'textfield',
		url: String.format(laboratree.links.inbox.send, '', ''),
		standardSubmit: true,
		defaults: {
			width: '864px'
		},

		tbar: [{
			text: 'Received',
			handler: function() {
				window.location = laboratree.links.inbox.received;
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'Sent',
			handler: function() {
				window.location = laboratree.links.inbox.sent;
			}
		},{
			xtype: 'tbseparator'
		},{
			text: 'Trash',
			handler: function() {
				window.location = laboratree.links.inbox.trash;
			}
		},{
			xtype: 'tbseparator'
		},{

			text: 'New Message',
			handler: function() {
				window.location = String.format(laboratree.links.inbox.send, '', '');
			}
		},{
			xtype: 'tbseparator'
		},{

			id: 'addressbook-button',
			text: 'Address Book',
			enableToggle: true,
			listeners: {
				toggle: function(button, pressed) {
					if(pressed) {
						laboratree.inbox.addressbook.win.show();
					} else {
						laboratree.inbox.addressbook.win.hide();
					}
				}
			}
		}],

		items: [this.combo,{
			id: 'subject',
			fieldLabel: 'Subject',
			name: 'data[Message][subject]'
		},{
			id: 'attachment',
			xtype: 'button',
			fieldLabel: 'Attachments',
			text: 'Add Attachment',
			width: 100,
			handler: function() {
				var attachment = {
					xtype: 'fileuploadfield',
					emptyText: 'Select an attachment',
					fieldLabel: 'Attachment',
					name: 'data[Attachment][attachment][]'
				};

				var message = Ext.getCmp('message');
				var i;
				for(i = 0; i < laboratree.inbox.send.form.items.items.length; i++) {
					if(laboratree.inbox.send.form.items.items[i] == message) {
						laboratree.inbox.send.form.insert(i, attachment);
						laboratree.inbox.send.form.doLayout();
						break;
					}
				}
			}
		},{
			id: 'message',
			xtype: 'htmleditor',
			enableColors: true,
			enableAlignments: false,
			//xtype: 'textarea',
			cls: 'inbox-body',
			fieldLabel: 'Message',
			hideLabel: true,
			name: 'data[Message][body]',
			anchor: '100% -100'
		}],
		
		buttons: [{
			text: 'Send',
			handler: function() {
				var fp = this.ownerCt.ownerCt;
				var form = fp.getForm();
				var to = Ext.getCmp('to');
				if(Ext.DomQuery.jsSelect('a[class=inbox-token]').length == 0) {
					to.addClass('field-invalid');
					return;
				}
				if(Ext.getCmp('to').isValid()) {
					form.submit();
				}
			}
		}]
	});
};

laboratree.inbox.AddressBook = function(data_url) {
	Ext.QuickTips.init();

	this.contacts = new Ext.data.JsonStore({
		root: 'contacts',
		idProperty: 'token',
		url: data_url,
		autoLoad: true,
		fields: [
			'id', 'name', 'token', 'type', 'image'
		]
	});

	this.contacts.setDefaultSort('name');

	this.sm = new Ext.grid.CheckboxSelectionModel();

	this.grid = new Ext.grid.GridPanel({
		id: 'addressbook',
		anchor: '100% 100%',

		store: this.contacts,

		sm: this.sm,
		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [
			this.sm,{
				id: 'name',
				header: 'Name',
				dataIndex: 'name',
				width: 100
			},{
				id: 'type',
				header: 'Type',
				dataIndex: 'type',
				width: 50,
				renderer: Ext.util.Format.capitalize
			}]
		}),

		viewConfig: {
			forceFit: true,
			scrollOffset: 1
		},

		bbar: new Ext.PagingToolbar({
			pageSize: 10,
			store: this.contacts,
			displayInfo: true,
			displayMsg: 'Contacts {0} - {1} / {2}',
			emptyMsg: 'No contacts'
		})
	});

	this.win = new Ext.Window({
		width: 400,
		height: 350,
		minWidth: 400,
		minHeight: 350,
		title: 'Address Book',
		layout: 'anchor',
		plain: true,
		closable: false,
		buttonAlign: 'center',

		items: this.grid,

		buttons: [{
			text: 'Ok',
			handler: function() {
				var selections = laboratree.inbox.addressbook.sm.getSelections();
				Ext.each(selections, function(record) {
					laboratree.inbox.send.combo.addLink(record);
				});

				Ext.getCmp('addressbook-button').toggle(false, true);
				laboratree.inbox.addressbook.win.hide();
			}
		},{
			text: 'Cancel',
			handler: function() {
				Ext.getCmp('addressbook-button').toggle(false, true);
				laboratree.inbox.addressbook.win.hide();
			}
		}],

		listeners: {
			beforeshow: function() {
				laboratree.inbox.addressbook.sm.clearSelections();

				/*
				var records = [];

				var tokens = Ext.query('.inbox-token');
				Ext.each(tokens, function(token) {
					var token = token.id.replace('token_', '');
					var record = laboratree.inbox.contacts.getById(token);
					if(record) {
						records.push(record);
					}
				});

				laboratree.inbox.addressbook.sm.selectRecords(records);
				*/
			}
		}
	});
};

laboratree.inbox.ContactBox = Ext.extend(Ext.form.ComboBox, {
	constructor: function(config) {
		this.linkTpl = new Ext.Template(
			'<a class="inbox-token" href="#" tabindex="-1" id="token_{token}">',
			'<input type="hidden" name="data[Inbox][tokens][]" value="{token}" />',
			'{name}',
			'<span class="inbox-token-remove" onclick="this.parentNode.parentNode.removeChild(this.parentNode);" ext:qtip="Click to Remove">&nbsp;</span>',
			'</a>'
		);
		this.linkTpl.compile();

		Ext.form.ComboBox.superclass.constructor.call(this, config);
	},
	setValue: function(v) {
		var record = this.findRecord(this.valueField, v);
		if(record) {
			this.addLink(record);
		}

		this.setRawValue('');
		this.lastSelectionText = '';
		this.value = '';

		return this;
	},
	addLink: function(record) {
		if(!Ext.get('token_' + record.data.token)) {
			var link = this.linkTpl.apply(record.data);
			Ext.DomHelper.insertBefore(this.el, link);
		}
	},
	removeLink: function(record) {
		if(Ext.get('token_' + record.data.token)) {
			/* TODO this variable is just to avoid an empty block */
			var nothing;
			//remove link
		}
	}
});

laboratree.inbox.templates = {};
laboratree.inbox.templates.group_invite = Ext.extend(Ext.Container, {
	constructor: function(config) {
		var message = config.message || {};
	
		var tpl = new Ext.Template([
			'{sender} has invited you to join the group "{group}"'
		]);
		tpl.compile();

		config = Ext.apply({
			id: 'inbox-choices',
			items: new laboratree.inbox.actions.AcceptDeny({
				inbox_id: message.id,
				message: tpl.apply(message.template_data)
			})
		}, config);

		laboratree.inbox.templates.group_request.superclass.constructor.call(this, config);
	}
});
laboratree.inbox.templates.group_request = Ext.extend(Ext.Container, {
	constructor: function(config) {
		var message = config.message || {};
	
		var tpl = new Ext.Template([
			'{sender} has requested to join the group "{group}"'
		]);
		tpl.compile();

		config = Ext.apply({
			id: 'inbox-choices',
			items: new laboratree.inbox.actions.AcceptDeny({
				inbox_id: message.id,
				message: tpl.apply(message.template_data)
			})
		}, config);

		laboratree.inbox.templates.group_request.superclass.constructor.call(this, config);
	}
});
laboratree.inbox.templates.project_invite = Ext.extend(Ext.Container, {
	constructor: function(config) {
		var message = config.message || {};
	
		var tpl = new Ext.Template([
			'{sender} has invited you to join the project "{project}"'
		]);
		tpl.compile();

		config = Ext.apply({
			id: 'inbox-choices',
			items: new laboratree.inbox.actions.AcceptDeny({
				inbox_id: message.id,
				message: tpl.apply(message.template_data)
			})
		}, config);

		laboratree.inbox.templates.project_request.superclass.constructor.call(this, config);
	}
});
laboratree.inbox.templates.project_request = Ext.extend(Ext.Container, {
	constructor: function(config) {
		var message = config.message || {};
	
		var tpl = new Ext.Template([
			'{sender} has requested to join the project "{project}"'
		]);
		tpl.compile();

		config = Ext.apply({
			id: 'inbox-choices',
			items: new laboratree.inbox.actions.AcceptDeny({
				inbox_id: message.id,
				message: tpl.apply(message.template_data)
			})
		}, config);

		laboratree.inbox.templates.project_request.superclass.constructor.call(this, config);
	}
});

laboratree.inbox.actions = {};
laboratree.inbox.actions.AcceptDeny = Ext.extend(Ext.Panel, {
	constructor: function(config) {
		var inbox_id = config.inbox_id || null;
		var message = config.message || null;
		
		config = Ext.apply({
			layout: 'fit',
			border: false,

			buttonAlign: 'center',	

			items: {
				xtype: 'container',
				cls: 'inbox-message',
				html: message
			},

			buttons: [{
				text: 'Accept',
				inbox_id: inbox_id,
				handler: function(button, e) {
					window.location = String.format(laboratree.links.inbox.accept, button.inbox_id);
				}
			},{
				text: 'Deny',
				inbox_id: inbox_id,
				handler: function(button, e) {
					window.location = String.format(laboratree.links.inbox.deny, button.inbox_id);
				}
			}]
		}, config);

		laboratree.inbox.actions.AcceptDeny.superclass.constructor.call(this, config);
	}
});

/**
 * Message Archive View
 */
laboratree.inbox.makeArchives = function(div, data_url) {
	laboratree.inbox.archives = new laboratree.inbox.Archives(div, data_url);
};

laboratree.inbox.Archives = function(div, data_url) {
	Ext.QuickTips.init();

	this.div = div;
	this.data_url = data_url;

	this.store = new Ext.data.JsonStore({
		root: 'messages',
		autoLoad: true,
		idProperty: 'id',
		url: data_url,
		remoteSort: true,
		fields: [
			'from', 'to', 'subject', {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
		]
	});	

	this.store.setDefaultSort('date', 'DESC');

	this.reader = new Ext.Panel({
		id: 'dashboard',
		title: 'Message Archives',
		renderTo: div,
		width: '100%',
		height: 600,

		layout: 'vbox',
		layoutConfig: {
			align: 'stretch',
			pack: 'start'
		},

		items: [{
			xtype: 'grid',
			flex: 1,

			store: this.store,
			loadMask: true,

			cm: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [{
					id: 'from',
					header: 'From',
					dataIndex: 'from',
					width: 115
				},{
					id: 'subject',
					header: 'Subject',
					dataIndex: 'subject',
					width: 575,
					renderer: laboratree.inbox.render.archive.subject
				},{
					id: 'date',
					header: 'Date',
					dataIndex: 'date',
					width: 120,
					renderer: Ext.util.Format.dateRenderer('m/d/Y g:i a')
				}]
			}),

			viewConfig: {
				forceFit: true,
				getRowClass: function(record, index) {
					return 'x-grid3-row-' + record.get('status');
				}
			},

			bbar: new Ext.PagingToolbar({
				pageSize: 11,
				store: this.store,
				displayInfo: true,
				displayMsg: 'Displaying message {0} - {1} of {2}',
				emptyMsg: 'No messages to display'
			})
		},{
			id: 'reading-pane',
			flex: 1,
			autoScroll: true,
			bodyStyle: 'padding: 5px;'
		}]
	});
};

laboratree.inbox.Archives.prototype.updateReader = function(archive_id) {
	laboratree.inbox.masks.archive = new Ext.LoadMask('reading-pane', {
		msg: 'Loading...'
	});
	laboratree.inbox.masks.archive.show();

	Ext.Ajax.request({
		url: laboratree.inbox.archives.data_url,
		params: {
			action: 'view',
			archive_id: archive_id
		},
		success: function(response, request) {
			var data = Ext.decode(response.responseText);
			if(!data)
			{
				request.failure(response, request);
				return;
			}

			if(!data.success) {
				request.failure(response, request);
				return;
			}

			if(!data.message) {
				request.failure(response, request);
				return;
			}

			var readingPane = Ext.getCmp('reading-pane');
			if(!readingPane) {
				request.failure(response, request);
				return;
			}	

			readingPane.update(data.message.body);

			laboratree.inbox.masks.archive.hide();
		},
		failure: function(response, request) {
			laboratree.inbox.masks.archive.hide();
		},
		scope: this
	});
};

/**
 * View Message Archive View
 */
laboratree.inbox.makeReadOnly = function(container_div, content_div, inbox_id) {
	laboratree.inbox.readOnly = new laboratree.inbox.ReadOnly(container_div, content_div, inbox_id);
};

laboratree.inbox.ReadOnly = function(container_div, content_div, inbox_id) {
	Ext.QuickTips.init();

	this.inbox_id = inbox_id;

	this.view = new Ext.Panel({
		id: 'view',
		title: 'View Archived Message',
		contentEl: content_div,
		renderTo: container_div,
		width: '100%',
		height: 600,
		layout: 'vbox',
		frame: true,
		bodyStyle: 'background-color: #ffffff; border: 2px solid #9cd2ec;'
	});
};

laboratree.inbox.render = {};
laboratree.inbox.render.archive = {};
laboratree.inbox.render.archive.subject = function(value, p, record)
{
	return String.format('<a href="#" onclick="laboratree.inbox.archives.updateReader({0}); return false;" title="{1}">{1}</a>', record.id, value);
};

laboratree.inbox.render.dashboard = {};
laboratree.inbox.render.dashboard.from = function(value, p, record) {
	if(record.data.group) {
		return record.data.group;
	} else if(record.data.project) {
		return record.data.project;
	} else {
		return value;
	}
};

laboratree.inbox.render.dashboard.subject = function(value, p, record) {
	return String.format('<a href="' + laboratree.links.inbox.view + '">{1}</a>', record.id, value);
};

laboratree.inbox.render.dashboard.attachments = function(value, p, record) {
	if(value && value.length > 0) {
		return '<img src="/img/icons/attachment_mail.gif" width="12" height="12" alt="A" />';
	}

	return '';
};

laboratree.inbox.render.view = {};
laboratree.inbox.render.view.from = function(value, message) {
	if(message.group_id) {
		return String.format('<a href="' + laboratree.links.groups.profile + '">{1}</a> (<a href="' + String.format(laboratree.links.users.profile, '{2}') + '">{3}</a>)', message.group_id, message.group, message.sender_id, message.from);
	} else if (message.project_id) {
		return String.format('<a href="' + laboratree.links.groups.profile + '">{1}</a> (<a href="' + String.format(laboratree.links.users.profile, '{2}') + '">{3}</a>)', message.project_id, message.project, message.sender_id, message.from);
	} else {
		return String.format('<a href="' + laboratree.links.users.profile + '">{1}</a>', message.sender_id, message.from);
	}
};

laboratree.inbox.render.view.to = function(value, message) {
	if(message.receiver_type == 'user') {
		return String.format('<a href="' + laboratree.links.users.profile + '">{1}</a>', message.receiver_id, message.to);
	} else if(message.receiver_type == 'group') {
		return String.format('<a href="' + laboratree.links.groups.profile + '">{1}</a>', message.receiver_id, message.to);
	} else if(message.receiver_type == 'project') {
		return String.format('<a href="' + laboratree.links.projects.profile + '">{1}</a>', message.receiver_id, message.to);
	} else {
		return value;
	}
};

laboratree.inbox.render.view.date = function(value, message) {
	var d = Date.parseDate(value, 'Y-m-d H:i:s');
	return d.format('F j, Y g:i a');
};

laboratree.inbox.render.view.body = function(value, message) {
	if(message.template) {
		if(laboratree.inbox.templates[message.template]) {
			return new laboratree.inbox.templates[message.template]({
				message: message
			});
		} else {
			return {
				html: value
			};
		}
	} else {
		return {
			html: value
		};
	}
};

laboratree.inbox.render.view.attachments = function(value, message) {
	var tpl = new Ext.Template(
		'<a class="inbox-attachment {class}" href="' + String.format(laboratree.links.inbox.attachment, '{id}') + '" title="{name}">{name}</a>'
	);
	tpl.compile();

	if(value && value.length > 0) {
		var panel = new Ext.Panel();

		Ext.each(value, function(attachment, index, allAttachments) {
			panel.add({
				html: tpl.apply(attachment),
				flex: 1
			});
		}, this);

		return panel;
	} else {
		return {};
	}
};

