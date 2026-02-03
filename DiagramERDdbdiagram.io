Table roles {
  id integer [primary key, increment]
  name varchar(50) [unique, not null]
}

Table instrument_types {
  id integer [primary key, increment]
  name varchar(30) [not null]
  string_count integer [not null]
}

Table keys {
  id integer [primary key, increment]
  key_name varchar(10) [not null]
  key_mode key_mode_enum
}

Table tunings {
  id integer [primary key, increment]
  tuning varchar(10) [not null]
  instrument_type_id integer [ref: > instrument_types.id]
}

Table users {
  id integer [primary key, increment]
  email varchar(255) [unique, not null]
  password varchar(255) [not null]
  id_role integer [ref: > roles.id]
}

Table user_profiles {
  id integer [primary key, increment]
  id_user integer [unique, not null, ref: - users.id]
  nickname varchar(100)
  bio text
}

Table songs {
  id integer [primary key, increment]
  name varchar(100) [not null]
  capo_fret integer [default: 0]
  instrument_type_id integer [ref: > instrument_types.id]
  key_id integer [ref: > keys.id]
  tuning_id integer [ref: > tunings.id]
  author_id integer [ref: > users.id]
  artist_name varchar(100)
  time_signature varchar(10) [default: '4/4']
  tempo integer [default: 120]
  song_text text [not null]
  created_at timestamp [default: `now()`]
}

Table chords {
  id integer [primary key, increment]
  name varchar(10) [not null]
  chord_diagram text
  instrument_type_id integer [ref: > instrument_types.id]
  tuning_id integer [ref: > tunings.id]
  author_id integer [ref: > users.id]
}

Table chords_for_songs {
  song_id integer [ref: > songs.id]
  chord_id integer [ref: > chords.id]
  
  Indexes {
    (song_id, chord_id) [pk]
  }
}