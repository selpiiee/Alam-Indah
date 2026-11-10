INSERT INTO settings(key,value) VALUES ('company','Alam Indah'),('warehouse','Kota Malang'),('address','Jl. Raya Singosari, Malang, Jawa Timur'),('default_min_stock','25');

INSERT INTO users (id,name,email,phone,password_hash,role,is_active,last_login_at) VALUES
 (1,'Rina Wulandari','rina@alamindah.co.id','0812 3000 1001','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','super_admin',TRUE,'2026-10-19 08:02'),
 (2,'Budi Santoso','budi@alamindah.co.id','0812 3456 7890','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','admin',TRUE,'2026-10-19 08:31'),
 (3,'Dewi Lestari','dewi@alamindah.co.id','0812 3000 1003','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','admin',TRUE,'2026-10-19 09:05'),
 (4,'Agus Prasetyo','agus@alamindah.co.id','0812 3000 1004','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','admin',TRUE,'2026-10-18 15:48'),
 (5,'Siti Rahmawati','siti@alamindah.co.id','0812 3000 1005','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','staff',TRUE,'2026-10-19 07:55'),
 (6,'Hadi Kurniawan','hadi@alamindah.co.id','0812 3000 1006','$2y$10$j.L606nFBVdG19SLbHZHBe/yO3nPugofL8tTfo1DLM5EEADdchyFK','staff',FALSE,'2026-09-02 16:20');
SELECT setval('users_id_seq',6);

INSERT INTO categories (id,name,code,description) VALUES
 (1,'Andesit','AND','Andesit bintik dan bakar untuk fasad, lantai, dan taman.'),
 (2,'Batu Candi & Paras','CND','Batu candi hitam dan paras untuk ornamen dan dinding.'),
 (3,'Marmer','MRM','Marmer dan travertine untuk interior dan fasad.'),
 (4,'Palimanan','PLM','Batu palimanan untuk dinding dan lantai eksterior.'),
 (5,'Granit','GRN','Granit lokal dan impor untuk lantai, countertop, dan fasad'),
 (6,'Batu Kali','KLI','Batu kali belah untuk pondasi dan dinding penahan.'),
 (7,'Batu Templek','TMP','Batu templek untuk dekorasi dinding.'),
 (8,'Koral & Kerikil','KRL','Koral sikat dan kerikil untuk taman dan carport.');
SELECT setval('categories_id_seq',8);

INSERT INTO suppliers (id,name,city,contact_person,phone,email,address,since) VALUES
 (1,'CV Karya Batu Mandiri','Blitar','Hendra Wijaya','0342 551 208','hendra@karyabatu.co.id','Jl. Raya Kademangan No. 14, Blitar, Jawa Timur','2025-01-10'),
 (2,'PT Marmer Nusantara','Makassar','Andi Rahman','0411 872 345','andi@marmernusantara.co.id','Jl. Perintis Kemerdekaan Km 12, Makassar, Sulawesi Selatan','2025-02-03'),
 (3,'UD Sumber Alam Jaya','Tulungagung','Sutrisno','0355 321 774','sutrisno@sumberalamjaya.id','Jl. Ki Mangunsarkoro No. 8, Tulungagung, Jawa Timur','2025-03-21'),
 (4,'CV Gunung Kapur Abadi','Gresik','Lilik Handayani','031 3981 026','lilik@gunungkapur.co.id','Jl. Veteran No. 77, Gresik, Jawa Timur','2025-04-15'),
 (5,'PT Borneo Granit Lestari','Berau','Yusuf Pratama','0554 202 931','yusuf@borneogranit.co.id','Jl. Pulau Derawan No. 3, Berau, Kalimantan Timur','2025-05-02'),
 (6,'UD Paras Mataram','Yogyakarta','Margono','0274 889 120','margono@parasmataram.co.id','Jl. Parangtritis Km 6, Bantul, Yogyakarta','2025-06-18'),
 (7,'CV Palimanan Raya','Cirebon','Dedi Suryana','0231 322 457','dedi@palimananraya.co.id','Jl. Palimanan Raya No. 21, Cirebon, Jawa Barat','2025-07-09');
SELECT setval('suppliers_id_seq',7);

INSERT INTO items (id,sku,name,category_id,supplier_id,stock,min_stock,price_per_ton,description,created_by,updated_by,created_at,updated_at) VALUES
 (1,'BA-AND-001','Batu Andesit Bintik',1,1,182,60,1250000,'Andesit bintik halus untuk lantai dan fasad.',1,2,'2026-03-12','2026-10-19 08:40'),
 (2,'BA-CND-002','Batu Candi Hitam',2,6,78,80,980000,'Batu candi hitam hasil belah alami, permukaan kasar, ukuran 20×20 cm hingga 30×30 cm.',1,3,'2026-03-12','2026-10-17 14:33'),
 (3,'BA-MRM-003','Marmer Ujung Pandang',3,2,92,50,3400000,'Marmer Ujung Pandang untuk interior dan fasad.',1,2,'2026-03-14','2026-10-18 11:20'),
 (4,'BA-PLM-004','Batu Palimanan Kuning',4,7,23,40,1050000,'Palimanan kuning untuk dinding eksterior.',1,1,'2026-03-15','2026-10-17 09:50'),
 (5,'BA-GRN-005','Granit Black Gold',5,5,67,30,4800000,'Granit hitam bermotif emas.',1,2,'2026-03-18','2026-10-14 08:30'),
 (6,'BA-PRS-006','Batu Paras Jogja',2,6,0,25,890000,'Paras Jogja untuk ukiran dan ornamen.',1,4,'2026-03-18','2026-10-18 16:05'),
 (7,'BA-KLI-007','Batu Kali Belah',6,3,245,100,520000,'Batu kali belah untuk pondasi.',1,3,'2026-03-20','2026-10-19 09:12'),
 (8,'BA-TMP-008','Batu Templek Putih',7,4,54,45,1120000,'Templek putih untuk dekorasi dinding.',1,1,'2026-03-22','2026-09-30 10:00'),
 (9,'BA-AND-009','Andesit Bakar Hitam',1,1,12,40,1480000,'Andesit bakar hitam permukaan kasar.',1,4,'2026-03-25','2026-10-16 15:10'),
 (10,'BA-KRL-010','Koral Sikat Putih',8,3,310,120,640000,'Koral sikat putih untuk taman dan carport.',1,2,'2026-03-26','2026-10-15 10:00'),
 (11,'BA-GRN-011','Granit Tropic Brown',5,5,0,20,4350000,'Granit coklat tropis.',1,3,'2026-04-02','2026-10-14 13:45'),
 (12,'BA-MRM-012','Marmer Travertine Krem',3,2,41,30,3150000,'Travertine krem untuk interior.',1,1,'2026-04-05','2026-09-28 11:00');
SELECT setval('items_id_seq',12);

INSERT INTO stock_transactions (id,reference,type,supplier_id,delivery_note,destination,recipient,reason,note,trans_date,created_by,created_at) VALUES
 (1,'IN-2610-015','in',5,'SJ-BGL-2210',NULL,NULL,NULL,NULL,'2026-10-14',2,'2026-10-14 08:30'),
 (2,'OUT-2610-020','out',NULL,NULL,'Proyek Perumahan Griya Kayutangan','Bpk. Rahmat','Penjualan',NULL,'2026-10-14',3,'2026-10-14 13:45'),
 (3,'IN-2610-016','in',3,'SJ-SAJ-0981',NULL,NULL,NULL,NULL,'2026-10-15',2,'2026-10-15 10:00'),
 (4,'OUT-2610-021','out',NULL,NULL,'Proyek Hotel Bromo','Ir. Hendro','Penjualan',NULL,'2026-10-16',4,'2026-10-16 15:10'),
 (5,'OUT-2610-022','out',NULL,NULL,'Toko Batu Alam Sejahtera','Ibu Sari','Penjualan',NULL,'2026-10-17',3,'2026-10-17 14:33'),
 (6,'ADJ-2610-004','adjust',NULL,NULL,NULL,NULL,'Penyesuaian','Selisih stok opname','2026-10-17',1,'2026-10-17 09:50'),
 (7,'IN-2610-017','in',2,'SJ-PTM-4410',NULL,NULL,NULL,NULL,'2026-10-18',2,'2026-10-18 11:20'),
 (8,'OUT-2610-023','out',NULL,NULL,'CV Bangun Cipta, Surabaya','Pak Dedi','Penjualan',NULL,'2026-10-18',4,'2026-10-18 16:05'),
 (9,'IN-2610-018','in',1,'SJ-KBM-5510',NULL,NULL,NULL,NULL,'2026-10-19',2,'2026-10-19 08:40'),
 (10,'OUT-2610-024','out',NULL,NULL,'PT Griya Asri Properti','Ir. Bambang Hartono','Penjualan','Pengiriman proyek villa, 2 rit','2026-10-19',3,'2026-10-19 09:12');
SELECT setval('stock_transactions_id_seq',10);

INSERT INTO stock_transaction_items (transaction_id,item_id,qty_change,balance_before,balance_after) VALUES
 (1,5,40,27,67),(2,11,-20,20,0),(3,10,80,230,310),(4,9,-18,30,12),(5,2,-22,100,78),
 (6,4,-2,25,23),(7,3,30,62,92),(8,6,-25,25,0),(9,1,60,122,182),(10,7,-35,280,245);

INSERT INTO audit_log (transaction_id,user_id,action,detail,created_at) VALUES
 (10,3,'Draf dibuat','Tujuan dan penerima diisi','2026-10-19 09:08:15'),
 (10,3,'Transaksi dikonfirmasi','Konfirmasi Pengeluaran','2026-10-19 09:12:40'),
 (10,3,'Stok diperbarui','Batu Kali Belah: 280 → 245 Ton','2026-10-19 09:12:41');
