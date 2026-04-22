# Tong hop thay doi admin cinema

## 1. Giao dien admin
- Layout admin duoc lam moi: sidebar, topbar, dashboard card, bang CRUD, form card, thong bao flash.
- Login admin duoc lam moi theo giao dien dark glassmorphism.
- Pagination chuyen sang Bootstrap 5 (`Paginator::useBootstrapFive()`).

## 2. CRUD phim
- Form phim gom cac khoi:
  - thong tin phim
  - the loai
  - dao dien / bien kich / dien vien
  - poster / trailer co preview
  - danh sach phien ban phim
- `poster_url` chi chap nhan URL anh truc tiep.
- `trailer_url` chi chap nhan YouTube/Vimeo.
- Du lieu nhan su duoc dong bo vao `people` va pivot `movie_people`.
- The loai duoc dong bo vao `movie_genres`.
- Phien ban phim duoc tao/cap nhat ngay trong form phim.

## 3. CRUD the loai
- Bo sung route admin categories.
- Sua lai controller/model de phu hop schema `genres` thuc te.

## 4. CRUD rap
- `opening_hours` khong con nhap JSON tay, chuyen thanh form theo tung ngay trong tuan.
- `timezone` chuyen sang danh sach chon san.

## 5. CRUD suat chieu
- Check trung lich cung phong o server side.
- Goi y gio ket thuc dua tren thoi luong phim o client side.

## 6. Logic bo tro
- `Person` model moi.
- `Movie` model bo sung quan he `genres`, `credits`, `directorCredits`, `writerCredits`, `castCredits`.
- `CinemaChainController` website doi sang validate URL.
