CREATE TABLE IF NOT EXISTS section(
    x INT UNIQUE,
    z INT UNIQUE,
    world_name VARCHAR(100),
    owner_name VARCHAR(30),
    owner_prefix VARCHAR(50),
    group_permission BIT(3),
    other_permission BIT(3),
    share_group JSON,
    UNIQUE coordinate(x, z, world_name)
);