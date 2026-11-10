DROP TABLE IF EXISTS audit_log, stock_transaction_items, stock_transactions, items, suppliers, categories, users, settings CASCADE;
DROP TYPE IF EXISTS user_role, txn_type;

CREATE TYPE user_role AS ENUM ('super_admin', 'admin', 'staff');
CREATE TYPE txn_type  AS ENUM ('in', 'out', 'adjust');

CREATE TABLE users (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(160) NOT NULL UNIQUE,
    phone         VARCHAR(30),
    password_hash VARCHAR(255) NOT NULL,
    role          user_role    NOT NULL DEFAULT 'staff',
    is_active     BOOLEAN      NOT NULL DEFAULT TRUE,
    must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at TIMESTAMPTZ,
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE,
    code        CHAR(3)     NOT NULL UNIQUE,
    description TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE suppliers (
    id             SERIAL PRIMARY KEY,
    name           VARCHAR(140) NOT NULL UNIQUE,
    city           VARCHAR(80)  NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    phone          VARCHAR(30)  NOT NULL,
    email          VARCHAR(160),
    address        TEXT,
    notes          TEXT,
    since          DATE,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE items (
    id            SERIAL PRIMARY KEY,
    sku           VARCHAR(30)  NOT NULL UNIQUE,
    name          VARCHAR(140) NOT NULL,
    category_id   INT NOT NULL REFERENCES categories(id),
    supplier_id   INT NOT NULL REFERENCES suppliers(id),
    unit          VARCHAR(10)  NOT NULL DEFAULT 'Ton',
    stock         NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (stock >= 0),
    min_stock     NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (min_stock >= 0),
    price_per_ton NUMERIC(14,0) NOT NULL DEFAULT 0 CHECK (price_per_ton >= 0),
    description   VARCHAR(250),
    created_by    INT REFERENCES users(id),
    updated_by    INT REFERENCES users(id),
    created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_supplier ON items(supplier_id);

CREATE TABLE stock_transactions (
    id            SERIAL PRIMARY KEY,
    reference     VARCHAR(30) NOT NULL UNIQUE,   -- IN-2610-019 / OUT-2610-024 / ADJ-2610-004
    type          txn_type    NOT NULL,
    supplier_id   INT REFERENCES suppliers(id),  -- incoming
    delivery_note VARCHAR(60),                   -- incoming: no. surat jalan
    destination   VARCHAR(160),                  -- outgoing: tujuan pengiriman
    recipient     VARCHAR(120),                  -- outgoing: penerima
    reason        VARCHAR(60),                   -- outgoing: alasan
    note          TEXT,
    trans_date    DATE        NOT NULL,
    created_by    INT NOT NULL REFERENCES users(id),
    created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_txn_date ON stock_transactions(trans_date);

CREATE TABLE stock_transaction_items (
    id             SERIAL PRIMARY KEY,
    transaction_id INT NOT NULL REFERENCES stock_transactions(id) ON DELETE CASCADE,
    item_id        INT NOT NULL REFERENCES items(id),
    qty_change     NUMERIC(12,2) NOT NULL,       -- + masuk / - keluar
    balance_before NUMERIC(12,2) NOT NULL,
    balance_after  NUMERIC(12,2) NOT NULL
);
CREATE INDEX idx_txi_item ON stock_transaction_items(item_id);

CREATE TABLE audit_log (
    id             SERIAL PRIMARY KEY,
    transaction_id INT REFERENCES stock_transactions(id) ON DELETE CASCADE,
    user_id        INT REFERENCES users(id),
    action         VARCHAR(80) NOT NULL,
    detail         VARCHAR(255),
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE settings (
    key   VARCHAR(60) PRIMARY KEY,
    value TEXT NOT NULL
);
