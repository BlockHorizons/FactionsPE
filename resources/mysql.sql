CREATE TABLE members (
  name VARCHAR(16) PRIMARY KEY UNIQUE,
  title CHAR,
  firstPlayed LONG,
  lastPlayed LONG,
  power INT
);
CREATE TABLE factions (
  name VARCHAR(16) UNIQUE,
  id VARCHAR(19) PRIMARY KEY UNIQUE,
  createdAt LONG,
  description TEXT,
  motd TEXT,
  powerBoost INT,
  bank INT,
  # Here we go lazy version: using TEXT strings instead of separated tables
  perms TEXT,
  flags TEXT,
  relationWishes TEXT,
  invitedPlayers TEXT,
  members TEXT
);
CREATE TABLE permissions (
  name VARCHAR(20) PRIMARY KEY UNIQUE,
  description TEXT,
  standard TEXT,
  territory BOOL,
  editable BOOL,
  visible BOOL,
  priority INT
);
CREATE TABLE flags (
  id VARCHAR(20) PRIMARY KEY UNIQUE,
  name VARCHAR(32) UNIQUE,
  priority INT,
  description TEXT,
  descriptionYes TEXT,
  descriptionNo TEXT,
  visible BOOL,
  editable BOOL,
  standard BOOL
);
CREATE TABLE plots (
  pos CHAR PRIMARY KEY UNIQUE,
  fid VARCHAR(20)
);