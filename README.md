# seenow_server

/** Creating Database / create database SeenowDB
/ Select the Database **/ use SeenowDB

create table users ( 
	id int(11) primary key auto_increment, 
	fullname varchar(50) not null, 
	email varchar(100) not null unique,
	encrypted_password varchar(80) not null,
	salt varchar(10) not null,
	created_at datetime,
	birthday date not null,
	gender varchar(1) not null,
	country varchar(30) not null,
	socialLoggedIn int(11) null,
	profilePic int(11) not null,
	FOREIGN KEY (profilePic) REFERENCES pictures(id)
);

create table feeds ( 
	id int(11) primary key auto_increment, 
	author_id int(11) not null, 
	foundUser_id int(11), 
	picture_id int(11) not null, 
	description varchar(255), 
	posted_at datetime, 
	FOREIGN KEY (author_id) REFERENCES users(id) 
);

create table likedFeed ( 
	id int(11) primary key auto_increment, 
	user_id int(11) not null, 
	FOREIGN KEY (id) REFERENCES feeds(id), 
	FOREIGN KEY (user_id) REFERENCES users(id) 
);

create table images ( 
	id int(11) primary key auto_increment, 
	name varchar(500) not null, 
	authorID int(11) not null, 
	FOREIGN key (authorID) REFERENCES users(id) 
);


/** Get all the feeds in what user with id = '1' is involved */ 

SELECT * FROM feeds as f 
	LEFT JOIN users as u on 
		((f.author_id = u.id or f.foundUser_id = u.id) and u.id != '1') 
	WHERE (f.author_id = '1' or f.foundUser_id = '1');

/** Get all users that user =1 has a relation */ 

SELECT u. FROM usersRelations as ur 
	LEFT JOIN users as u on 
			(ur.user2_id = u.id) 
	where ur.user1_id = '1';

/** Get all feeds from friends of user1 */ 

select f., u., ur. FROM usersRelations as ur 
	LEFT JOIN feeds as f on 
		(f.foundUser_id = ur.user2_id or f.author_id = ur.user2_id) 
	LEFT JOIN users as u on 
		(u.id = ur.user2_id and (u.id = f.foundUser_id or u.id = f.author_id )) 
	WHERE ur.user1_id = '2';
