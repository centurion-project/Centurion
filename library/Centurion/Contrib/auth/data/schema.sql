SET foreign_key_checks = 0;

drop table if exists auth_user;

drop table if exists auth_user_permission;

drop table if exists auth_belong;

drop table if exists auth_group;

drop table if exists auth_group_permission;

drop table if exists auth_permission;

/*==============================================================*/
/* Table: auth_user                                       */
/*==============================================================*/
create table auth_user
(
   id                   int(11) unsigned not null auto_increment,
   username             varchar(255) not null,
   first_name           varchar(30) default null,
   last_name            varchar(30) default null,
   email                varchar(255) default null,
   password             varchar(128) not null,
   salt                 varchar(128) default null,
   algorithm            varchar(128) not null,
   can_be_deleted       int(1) unsigned not null default 1,
   is_active            int(1) unsigned not null default 0,
   is_super_admin       int(1) unsigned not null default 0,
   is_staff             int(1) unsigned not null default 0,
   created_at           timestamp NOT NULL default '0000-00-00 00:00:00',
   last_login           timestamp NOT NULL default '0000-00-00 00:00:00',
   updated_at           timestamp,
   user_parent_id       int(11) unsigned default null,
   primary key (id)
)
ENGINE=InnoDB default charset=utf8;

ALTER TABLE  `auth_user` ADD INDEX (  `can_be_deleted` );
ALTER TABLE  `auth_user` ADD INDEX (  `is_active` );
ALTER TABLE  `auth_user` ADD INDEX (  `is_super_admin` );
ALTER TABLE  `auth_user` ADD INDEX (  `is_staff` );
ALTER TABLE  `auth_user` ADD INDEX (  `email` );
ALTER TABLE  `auth_user` ADD INDEX (  `username` );

/*==============================================================*/
/* Table: auth_user_permission                                  */
/*==============================================================*/
create table auth_user_permission
(
   user_id             int(11) unsigned not null,
   permission_id        int(11) unsigned not null,
   primary key (user_id, permission_id)
)
ENGINE=InnoDB default charset=utf8;

/*==============================================================*/
/* Table: auth_belong                                           */
/*==============================================================*/
create table auth_belong
(
   user_id             int(11) unsigned not null,
   group_id             int(11) unsigned not null,
   primary key (user_id, group_id)
)
ENGINE=InnoDB default charset=utf8;

/*==============================================================*/
/* Table: auth_group                                            */
/*==============================================================*/
create table auth_group
(
   id                   int(11) unsigned not null auto_increment,
   name                 varchar(255) not null,
   description          text,
   group_parent_id      int(11) unsigned,
   primary key (id)
)
ENGINE=InnoDB default charset=utf8;

/*==============================================================*/
/* Table: auth_group_permission                            */
/*==============================================================*/
create table auth_group_permission
(
   group_id             int(11) unsigned not null,
   permission_id        int(11) unsigned not null,
   primary key (group_id, permission_id)
)
ENGINE=InnoDB default charset=utf8;

/*==============================================================*/
/* Table: auth_permission                                  */
/*==============================================================*/
create table auth_permission
(
   id                   int(11) unsigned not null auto_increment,
   name                 varchar(255) not null,
   description          varchar(255) not null,
   primary key (id)
)
ENGINE=InnoDB default charset=utf8;

alter table auth_user add constraint fk_user__user_parent_id___user__id foreign key (user_parent_id)
      references auth_user (id) on delete restrict on update restrict;

alter table auth_user_permission add constraint fk_permission__user_id___user__id foreign key (user_id)
      references auth_user (id) on delete restrict on update restrict;

alter table auth_user_permission add constraint fk_persmission__action_id___action__id foreign key (permission_id)
      references auth_permission (id) on delete restrict on update restrict;

alter table auth_belong add constraint fk_belong__group_id___group__user_id foreign key (group_id)
      references auth_group (id) on delete restrict on update restrict;

alter table auth_belong add constraint fk_reference_32 foreign key (user_id)
      references auth_user (id) on delete restrict on update restrict;

alter table auth_group add constraint fk_group__group_parent_id___group__id foreign key (group_parent_id)
      references auth_group (id) on delete restrict on update restrict;

alter table auth_group_permission add constraint fk_group_permission__group_id___group__id foreign key (group_id)
      references auth_group (id) on delete restrict on update restrict;

alter table auth_group_permission add constraint fk_group_permission__permission_id___permission__id foreign key (permission_id)
      references auth_permission (id) on delete restrict on update restrict;