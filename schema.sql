
create table users
(
    id int auto_increment,
    name varchar(64) not null,
    email varchar(256) not null,
    constraint users_pk
        primary key (id),
    constraint users_email
        unique (email)
);

