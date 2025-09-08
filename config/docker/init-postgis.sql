CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

CREATE USER postgres WITH PASSWORD 'postgres';

-- CREATE SCHEMA public;
-- GRANT ALL ON SCHEMA public TO postgres;
-- GRANT ALL ON SCHEMA public TO public;
--
-- ALTER DATABASE rede_plastica REFRESH COLLATION VERSION;