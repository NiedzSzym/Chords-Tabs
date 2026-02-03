
CREATE TYPE key_mode_enum AS ENUM ('major', 'minor');

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE instrument_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    string_count INTEGER NOT NULL
);


CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_role INTEGER NOT NULL REFERENCES roles(id)
);

CREATE TABLE user_profiles (
    id SERIAL PRIMARY KEY,
    id_user INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    nickname VARCHAR(100),
    bio TEXT
);


CREATE TABLE keys (
    id SERIAL PRIMARY KEY,
    key_name VARCHAR(10) NOT NULL,
    key_mode key_mode_enum
);

CREATE TABLE tunings (
    id SERIAL PRIMARY KEY,
    tuning VARCHAR(10) NOT NULL,
    instrument_type_id INTEGER NOT NULL REFERENCES instrument_types(id)
);

CREATE TABLE songs (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capo_fret INTEGER DEFAULT 0,
    instrument_type_id INTEGER NOT NULL REFERENCES instrument_types(id),
    key_id INTEGER REFERENCES keys(id),
    tuning_id INTEGER NOT NULL REFERENCES tunings(id),
    author_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    artist_name VARCHAR(100),
    time_signature VARCHAR(10) DEFAULT '4/4',
    tempo INTEGER DEFAULT 120,
    song_text TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chords (
    id SERIAL PRIMARY KEY,
    name VARCHAR(10) NOT NULL,
    chord_diagram TEXT,
    instrument_type_id INTEGER NOT NULL REFERENCES instrument_types(id),
    tuning_id INTEGER NOT NULL REFERENCES tunings(id),
    author_id INTEGER REFERENCES users(id) ON DELETE CASCADE
);

 
CREATE TABLE chords_for_songs (
    song_id INTEGER REFERENCES songs(id) ON DELETE CASCADE,
    chord_id INTEGER REFERENCES chords(id) ON DELETE CASCADE,
    PRIMARY KEY (song_id, chord_id)
);

CREATE OR REPLACE FUNCTION update_modified_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_song_timestamp
BEFORE UPDATE ON songs
FOR EACH ROW
EXECUTE FUNCTION update_modified_column();

INSERT INTO roles (name) VALUES ('admin');
INSERT INTO roles (name) VALUES ('user');