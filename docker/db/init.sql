--
-- PostgreSQL database dump
--

\restrict c4ghld2gxMX0DtpPfyg6ZyhgUT2NcZEmxh2vR5bKlvzt1H11L4AFQkeNMC3pc2V

-- Dumped from database version 18.1 (Debian 18.1-1.pgdg13+2)
-- Dumped by pg_dump version 18.1 (Debian 18.1-1.pgdg13+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_id_role_fkey;
ALTER TABLE IF EXISTS ONLY public.user_profiles DROP CONSTRAINT IF EXISTS user_profiles_id_user_fkey;
ALTER TABLE IF EXISTS ONLY public.tunings DROP CONSTRAINT IF EXISTS tunings_instrument_type_id_fkey;
ALTER TABLE IF EXISTS ONLY public.songs DROP CONSTRAINT IF EXISTS songs_tuning_id_fkey;
ALTER TABLE IF EXISTS ONLY public.songs DROP CONSTRAINT IF EXISTS songs_key_id_fkey;
ALTER TABLE IF EXISTS ONLY public.songs DROP CONSTRAINT IF EXISTS songs_instrument_type_id_fkey;
ALTER TABLE IF EXISTS ONLY public.songs DROP CONSTRAINT IF EXISTS songs_author_id_fkey;
ALTER TABLE IF EXISTS ONLY public.chords DROP CONSTRAINT IF EXISTS chords_tuning_id_fkey;
ALTER TABLE IF EXISTS ONLY public.chords DROP CONSTRAINT IF EXISTS chords_instrument_type_id_fkey;
ALTER TABLE IF EXISTS ONLY public.chords_for_songs DROP CONSTRAINT IF EXISTS chords_for_songs_song_id_fkey;
ALTER TABLE IF EXISTS ONLY public.chords_for_songs DROP CONSTRAINT IF EXISTS chords_for_songs_chord_id_fkey;
ALTER TABLE IF EXISTS ONLY public.chords DROP CONSTRAINT IF EXISTS chords_author_id_fkey;
DROP TRIGGER IF EXISTS trigger_format_song_data ON public.songs;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_email_key;
ALTER TABLE IF EXISTS ONLY public.user_profiles DROP CONSTRAINT IF EXISTS user_profiles_pkey;
ALTER TABLE IF EXISTS ONLY public.user_profiles DROP CONSTRAINT IF EXISTS user_profiles_id_user_key;
ALTER TABLE IF EXISTS ONLY public.tunings DROP CONSTRAINT IF EXISTS tunings_pkey;
ALTER TABLE IF EXISTS ONLY public.songs DROP CONSTRAINT IF EXISTS songs_pkey;
ALTER TABLE IF EXISTS ONLY public.roles DROP CONSTRAINT IF EXISTS roles_pkey;
ALTER TABLE IF EXISTS ONLY public.roles DROP CONSTRAINT IF EXISTS roles_name_key;
ALTER TABLE IF EXISTS ONLY public.keys DROP CONSTRAINT IF EXISTS keys_pkey;
ALTER TABLE IF EXISTS ONLY public.instrument_types DROP CONSTRAINT IF EXISTS instrument_types_pkey;
ALTER TABLE IF EXISTS ONLY public.chords DROP CONSTRAINT IF EXISTS chords_pkey;
ALTER TABLE IF EXISTS ONLY public.chords_for_songs DROP CONSTRAINT IF EXISTS chords_for_songs_pkey;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.user_profiles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tunings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.songs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.roles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.keys ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.instrument_types ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.chords ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP SEQUENCE IF EXISTS public.user_profiles_id_seq;
DROP SEQUENCE IF EXISTS public.tunings_id_seq;
DROP SEQUENCE IF EXISTS public.songs_id_seq;
DROP VIEW IF EXISTS public.song_details_view;
DROP TABLE IF EXISTS public.tunings;
DROP TABLE IF EXISTS public.songs;
DROP SEQUENCE IF EXISTS public.roles_id_seq;
DROP TABLE IF EXISTS public.roles;
DROP SEQUENCE IF EXISTS public.keys_id_seq;
DROP TABLE IF EXISTS public.keys;
DROP SEQUENCE IF EXISTS public.instrument_types_id_seq;
DROP TABLE IF EXISTS public.instrument_types;
DROP SEQUENCE IF EXISTS public.chords_id_seq;
DROP TABLE IF EXISTS public.chords_for_songs;
DROP TABLE IF EXISTS public.chords;
DROP VIEW IF EXISTS public.auth_users_view;
DROP TABLE IF EXISTS public.users;
DROP TABLE IF EXISTS public.user_profiles;
DROP FUNCTION IF EXISTS public.format_song_data();
DROP TYPE IF EXISTS public.key_mode_enum;
--
-- Name: key_mode_enum; Type: TYPE; Schema: public; Owner: -
--

CREATE TYPE public.key_mode_enum AS ENUM (
    'major',
    'minor'
);


--
-- Name: format_song_data(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.format_song_data() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Usuwa zb─Ödne spacje
    NEW.name = TRIM(NEW.name);
    -- Formatuj imi─Ö artysty (wielka litera na pocz─ůtku)
    NEW.artist_name = INITCAP(TRIM(NEW.artist_name));
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: user_profiles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_profiles (
    id integer NOT NULL,
    id_user integer NOT NULL,
    nickname character varying(100),
    bio text
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    id_role integer NOT NULL
);


--
-- Name: auth_users_view; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.auth_users_view AS
 SELECT u.id,
    u.email,
    u.password,
    u.id_role,
    p.nickname,
    p.bio
   FROM (public.users u
     LEFT JOIN public.user_profiles p ON ((u.id = p.id_user)));


--
-- Name: chords; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.chords (
    id integer NOT NULL,
    name character varying(10) NOT NULL,
    chord_diagram text,
    instrument_type_id integer NOT NULL,
    tuning_id integer NOT NULL,
    author_id integer
);


--
-- Name: chords_for_songs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.chords_for_songs (
    song_id integer NOT NULL,
    chord_id integer NOT NULL
);


--
-- Name: chords_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.chords_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: chords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.chords_id_seq OWNED BY public.chords.id;


--
-- Name: instrument_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.instrument_types (
    id integer NOT NULL,
    name character varying(30) NOT NULL,
    string_count integer NOT NULL
);


--
-- Name: instrument_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.instrument_types_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: instrument_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.instrument_types_id_seq OWNED BY public.instrument_types.id;


--
-- Name: keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.keys (
    id integer NOT NULL,
    key_name character varying(10) NOT NULL,
    key_mode public.key_mode_enum
);


--
-- Name: keys_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.keys_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: keys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.keys_id_seq OWNED BY public.keys.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    name character varying(50) NOT NULL
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: songs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.songs (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    capo_fret integer DEFAULT 0,
    instrument_type_id integer NOT NULL,
    key_id integer,
    tuning_id integer NOT NULL,
    author_id integer NOT NULL,
    artist_name character varying(100),
    song_text text NOT NULL,
    tempo integer DEFAULT 120,
    time_signature character varying(10) DEFAULT '4/4'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: tunings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tunings (
    id integer NOT NULL,
    tuning character varying(10) NOT NULL,
    instrument_type_id integer NOT NULL
);


--
-- Name: song_details_view; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.song_details_view AS
 SELECT s.id,
    s.name,
    s.artist_name,
    s.song_text,
    s.capo_fret,
    s.tempo,
    s.time_signature,
    s.created_at,
    s.author_id,
    k.key_name,
    k.key_mode,
    i.name AS instrument_name,
    t.tuning,
    t.id AS tuning_id
   FROM (((public.songs s
     LEFT JOIN public.keys k ON ((s.key_id = k.id)))
     LEFT JOIN public.instrument_types i ON ((s.instrument_type_id = i.id)))
     LEFT JOIN public.tunings t ON ((s.tuning_id = t.id)));


--
-- Name: songs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.songs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: songs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.songs_id_seq OWNED BY public.songs.id;


--
-- Name: tunings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tunings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tunings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tunings_id_seq OWNED BY public.tunings.id;


--
-- Name: user_profiles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.user_profiles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_profiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.user_profiles_id_seq OWNED BY public.user_profiles.id;


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: chords id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords ALTER COLUMN id SET DEFAULT nextval('public.chords_id_seq'::regclass);


--
-- Name: instrument_types id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instrument_types ALTER COLUMN id SET DEFAULT nextval('public.instrument_types_id_seq'::regclass);


--
-- Name: keys id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.keys ALTER COLUMN id SET DEFAULT nextval('public.keys_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: songs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs ALTER COLUMN id SET DEFAULT nextval('public.songs_id_seq'::regclass);


--
-- Name: tunings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tunings ALTER COLUMN id SET DEFAULT nextval('public.tunings_id_seq'::regclass);


--
-- Name: user_profiles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_profiles ALTER COLUMN id SET DEFAULT nextval('public.user_profiles_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: chords; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.chords (id, name, chord_diagram, instrument_type_id, tuning_id, author_id) FROM stdin;
15	C	[-1, 3, 2, 0, 1, 0]	1	1	\N
16	D	[-1, -1, 0, 2, 3, 2]	1	1	\N
17	E	[0, 2, 2, 1, 0, 0]	1	1	\N
18	F	[-1, -1, 3, 2, 1, 1]	1	1	\N
19	G	[3, 2, 0, 0, 0, 3]	1	1	\N
20	A	[-1, 0, 2, 2, 2, 0]	1	1	\N
21	B	[-1, 2, 4, 4, 4, 2]	1	1	\N
22	Cm	[-1, 3, 5, 5, 4, 3]	1	1	\N
23	Dm	[-1, -1, 0, 2, 3, 1]	1	1	\N
24	Em	[0, 2, 2, 0, 0, 0]	1	1	\N
25	Fm	[1, 3, 3, 1, 1, 1]	1	1	\N
26	Gm	[3, 5, 5, 3, 3, 3]	1	1	\N
27	Am	[-1, 0, 2, 2, 1, 0]	1	1	\N
28	Bm	[-1, 2, 4, 4, 3, 2]	1	1	\N
\.


--
-- Data for Name: chords_for_songs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.chords_for_songs (song_id, chord_id) FROM stdin;
\.


--
-- Data for Name: instrument_types; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.instrument_types (id, name, string_count) FROM stdin;
1	Guitar	6
2	Ukulele	4
3	Bass Guitar	4
\.


--
-- Data for Name: keys; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.keys (id, key_name, key_mode) FROM stdin;
1	C	major
2	C	minor
3	C#	major
4	C#	minor
5	Db	major
6	Db	minor
7	D	major
8	D	minor
9	Eb	major
10	Eb	minor
11	E	major
12	E	minor
13	F	major
14	F	minor
15	F#	major
16	F#	minor
17	Gb	major
18	Gb	minor
19	G	major
20	G	minor
21	Ab	major
22	Ab	minor
23	A	major
24	A	minor
25	Bb	major
26	Bb	minor
27	B	major
28	B	minor
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, name) FROM stdin;
1	admin
2	user
\.


--
-- Data for Name: songs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.songs (id, name, capo_fret, instrument_type_id, key_id, tuning_id, author_id, artist_name, song_text, tempo, time_signature, created_at) FROM stdin;
1	Wlaz┼é kotek na p┼éotek	0	1	\N	1	1	Ludowe	C] Wlaz┼é kotek na [G] p┼éotek [C] i mruga,\r\n[F] ┼éadna to [C] piosenka, [G] nie [C] d┼éuga.\r\n[C] Nie d┼éuga, [G] nie kr├│tka, [C] lecz w sam raz,\r\n[F] za┼Ťpiewaj [C] koteczku [G] jeszcze [C] raz.	120	4/4	2026-02-03 17:27:42.635613
2	Simple Rock Riff	2	1	24	1	1	Demo Band	Intro: [Am] [G] [F] [E]\r\n\r\nVerse 1:\r\n[Am] Standing in the rain, [G] waiting for the train.\r\n[F] Got no place to go, [E] time is moving slow.\r\n\r\nChorus:\r\n[Am] Rock and Roll, [C] save my soul!\r\n[D] Let it burn, [F] never learn!	120	4/4	2026-02-03 17:28:35.916996
\.


--
-- Data for Name: tunings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tunings (id, tuning, instrument_type_id) FROM stdin;
1	E Standard	1
2	GCEA (Std)	2
3	ADF#B	2
4	Drop D	1
5	Open G	1
6	E Standard	3
7	Drop D	3
\.


--
-- Data for Name: user_profiles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_profiles (id, id_user, nickname, bio) FROM stdin;
1	1	demo	\N
2	2	admin	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, email, password, id_role) FROM stdin;
1	demo@chords.com	$2y$10$nuQ8mrDraA8rWzmQTil2SegHUc7mvoDpH88fj6RpRlFEApn1PwO2i	2
2	admin@admin.com	$2y$10$1qOFaYSonRv/L5SoNhA2UuY.E7lgbhn13re5ZQ21xozgAgOEw3ds2	1
\.


--
-- Name: chords_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.chords_id_seq', 28, true);


--
-- Name: instrument_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.instrument_types_id_seq', 3, true);


--
-- Name: keys_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.keys_id_seq', 28, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 2, true);


--
-- Name: songs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.songs_id_seq', 2, true);


--
-- Name: tunings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tunings_id_seq', 7, true);


--
-- Name: user_profiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_profiles_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2, true);


--
-- Name: chords_for_songs chords_for_songs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords_for_songs
    ADD CONSTRAINT chords_for_songs_pkey PRIMARY KEY (song_id, chord_id);


--
-- Name: chords chords_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords
    ADD CONSTRAINT chords_pkey PRIMARY KEY (id);


--
-- Name: instrument_types instrument_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.instrument_types
    ADD CONSTRAINT instrument_types_pkey PRIMARY KEY (id);


--
-- Name: keys keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.keys
    ADD CONSTRAINT keys_pkey PRIMARY KEY (id);


--
-- Name: roles roles_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_key UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: songs songs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs
    ADD CONSTRAINT songs_pkey PRIMARY KEY (id);


--
-- Name: tunings tunings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tunings
    ADD CONSTRAINT tunings_pkey PRIMARY KEY (id);


--
-- Name: user_profiles user_profiles_id_user_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_id_user_key UNIQUE (id_user);


--
-- Name: user_profiles user_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: songs trigger_format_song_data; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trigger_format_song_data BEFORE INSERT ON public.songs FOR EACH ROW EXECUTE FUNCTION public.format_song_data();


--
-- Name: chords chords_author_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords
    ADD CONSTRAINT chords_author_id_fkey FOREIGN KEY (author_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chords_for_songs chords_for_songs_chord_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords_for_songs
    ADD CONSTRAINT chords_for_songs_chord_id_fkey FOREIGN KEY (chord_id) REFERENCES public.chords(id) ON DELETE CASCADE;


--
-- Name: chords_for_songs chords_for_songs_song_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords_for_songs
    ADD CONSTRAINT chords_for_songs_song_id_fkey FOREIGN KEY (song_id) REFERENCES public.songs(id) ON DELETE CASCADE;


--
-- Name: chords chords_instrument_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords
    ADD CONSTRAINT chords_instrument_type_id_fkey FOREIGN KEY (instrument_type_id) REFERENCES public.instrument_types(id);


--
-- Name: chords chords_tuning_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chords
    ADD CONSTRAINT chords_tuning_id_fkey FOREIGN KEY (tuning_id) REFERENCES public.tunings(id);


--
-- Name: songs songs_author_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs
    ADD CONSTRAINT songs_author_id_fkey FOREIGN KEY (author_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: songs songs_instrument_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs
    ADD CONSTRAINT songs_instrument_type_id_fkey FOREIGN KEY (instrument_type_id) REFERENCES public.instrument_types(id);


--
-- Name: songs songs_key_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs
    ADD CONSTRAINT songs_key_id_fkey FOREIGN KEY (key_id) REFERENCES public.keys(id);


--
-- Name: songs songs_tuning_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.songs
    ADD CONSTRAINT songs_tuning_id_fkey FOREIGN KEY (tuning_id) REFERENCES public.tunings(id);


--
-- Name: tunings tunings_instrument_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tunings
    ADD CONSTRAINT tunings_instrument_type_id_fkey FOREIGN KEY (instrument_type_id) REFERENCES public.instrument_types(id);


--
-- Name: user_profiles user_profiles_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: users users_id_role_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_id_role_fkey FOREIGN KEY (id_role) REFERENCES public.roles(id);


--
-- PostgreSQL database dump complete
--

\unrestrict c4ghld2gxMX0DtpPfyg6ZyhgUT2NcZEmxh2vR5bKlvzt1H11L4AFQkeNMC3pc2V

