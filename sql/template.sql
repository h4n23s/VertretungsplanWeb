create table cache
(
    `date-offset` tinyint not null,
    updated       bigint  not null,
    data          text    not null,
    constraint `date-offset-unique`
        unique (`date-offset`)
)
    charset = utf8;

alter table cache
    add primary key (`date-offset`);
