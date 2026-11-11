# Dokumentasi Skema Database KlikObat (Kelompok 22)

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    KATEGORI ||--o{ OBAT : "memiliki (1:N)"
    
    KATEGORI {
        int id_kategori PK "AUTO_INCREMENT"
        varchar_100 nama_kategori "NOT NULL, UNIQUE"
    }

    OBAT {
        int id_obat PK "AUTO_INCREMENT"
        varchar_150 nama_obat "NOT NULL"
        int id_kategori FK "ON DELETE RESTRICT"
        int stok "NOT NULL, DEFAULT 0"
        text indikasi "NOT NULL"
        text aturan_pakai "NOT NULL"
        text peringatan "NOT NULL"
    }

    ADMIN {
        int id_admin PK "AUTO_INCREMENT"
        varchar_50 username "NOT NULL, UNIQUE"
        varchar_255 password "NOT NULL (Bcrypt)"
    }