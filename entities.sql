CREATE TABLE IF NOT EXISTS users(
    uid VARCHAR(32) PRIMARY KEY,
    username TEXT NOT NULL,
    email TEXT NOT NULL,
    passwordHash TEXT NOT NULL,
    isAccountConfirmed BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS cats(
    uid VARCHAR(32) PRIMARY KEY,
    name TEXT NOT NULL,
    age INTEGER,
    description TEXT,
    whenLastSeen DATE,
    whereLastSeen TEXT,
    race TEXT,
    furColor TEXT,
    weight INTEGER,
    isStray BOOLEAN,
    image BLOB,
    price INTEGER,
    owner VARCHAR(32),
    FOREIGN KEY(owner) REFERENCES users(uid)
);

CREATE TABLE cartItems(
    uid VARCHAR(32) PRIMARY KEY,
    owner VARCHAR(32), cat VARCHAR(32),
    FOREIGN KEY(owner) REFERENCES users(uid),
    FOREIGN KEY(cat) REFERENCES cats(uid)
);

CREATE TABLE wishListItems(
    uid VARCHAR(32) PRIMARY KEY,
    owner VARCHAR(32), cat VARCHAR(32),
    FOREIGN KEY(owner) REFERENCES users(uid),
    FOREIGN KEY(cat) REFERENCES cats(uid)
);
