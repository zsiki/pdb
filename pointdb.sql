-- clean up
DROP TABLE IF EXISTS pdb_coords;
DROP TABLE IF EXISTS pdb_obs;
DROP TABLE IF EXISTS pdb_met;
DROP TABLE IF EXISTS pdb_points;
DROP TABLE IF EXISTS pdb_types;
DROP TABLE IF EXISTS pdb_projections;

-- table for projection name by epsg
CREATE TABLE pdb_projections(
	epsg int NOT NULL PRIMARY KEY,
	name varchar(20) NOT NULL CHECK (char_length(name) > 0)
);

-- table for point types
CREATE TABLE pdb_types(
	id serial NOT NULL PRIMARY KEY,
	type varchar(20) NOT NULL UNIQUE
);

-- table for general point data
CREATE TABLE pdb_points(
	id serial PRIMARY KEY NOT NULL,
	name varchar(30) unique NOT NULL CHECK (char_length(name) > 0),
	type_id int NOT NULL REFERENCES pdb_types(id),
	code char(4) NOT NULL DEFAULT 'ATR',
	pc double precision NOT NULL DEFAULT 0,
	remark varchar(255)
);

-- table for coordinates in different projection and time
CREATE TABLE pdb_coords(
	point_id int NOT NULL REFERENCES pdb_points(id),
	datetime timestamp NOT NULL,
	epsg int NOT NULL REFERENCES pdb_projections(epsg),
	east double precision,
	north double precision,
	elev double precision,
	CONSTRAINT coords_key PRIMARY KEY (point_id, datetime),
	check ((coalesce(east, -999999) != -999999 and coalesce(north, -999999) != -999999) or
		coalesce(elev, -999999) != -999999)
);

-- table for observations
CREATE TABLE pdb_obs (
	point_id int NOT NULL REFERENCES pdb_points(id),
	datetime timestamp NOT NULL,
	hz double precision NOT NULL,
	v double precision NOT NULL,
	distance double precision,
	long_inc double precision,
	cros_inc double precision,
	CONSTRAINT pkey_obs PRIMARY KEY (point_id, datetime)
);

CREATE TABLE pdb_met (
	point_id int NOT NULL REFERENCES pdb_points(id),
	datetime timestamp NOT NULL,
	temp double precision,
	wet double precision,
	pres double precision,
	CONSTRAINT pkey_met PRIMARY KEY (point_id, datetime)
);
