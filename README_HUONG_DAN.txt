HUONG DAN AP DUNG NHANH

1. Giai nen file zip nay.
2. Copy de cac tep vao dung du an Laravel cua ban theo dung cau truc thu muc.
3. Neu ban dang chay Laragon, dung va mo lai app hoac chay:
   php artisan optimize:clear
4. Dang nhap admin va kiem tra cac man hinh:
   - /admin/movies
   - /admin/movie-versions
   - /admin/categories
   - /admin/cinemas
   - /admin/auditoriums
   - /admin/shows

CAC DIEM DA SUA CHINH
- Giao dien admin duoc lam lai theo style dashboard sang, card va table dep hon.
- CRUD phim duoc nang cap:
  + Quan ly the loai gan voi genres
  + Quan ly dao dien, bien kich, dien vien gan voi people/movie_people
  + Quan ly danh sach phien ban phim ngay trong form phim
  + Preview poster va trailer
  + Trailer chi chap nhan YouTube/Vimeo
  + Poster chi chap nhan URL anh truc tiep
- CRUD rap duoc doi tu opening_hours JSON sang form theo ngay.
- CRUD suat chieu co check trung lich cung phong.
- Da bo sung route categories va menu the loai.
