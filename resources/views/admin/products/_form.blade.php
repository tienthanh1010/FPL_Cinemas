<div class="card">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Danh mục</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">SKU</label>
            <input class="form-control" name="sku" value="{{ old('sku', $product->sku) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Tên sản phẩm / combo</label>
            <input class="form-control" name="name" value="{{ old('name', $product->name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Đơn vị</label>
            <input class="form-control" name="unit" value="{{ old('unit', $product->unit ?: 'ITEM') }}" required>
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả / thành phần combo</label>
            <textarea class="form-control" rows="3" name="attributes_text">{{ old('attributes_text', $product->attributes['description'] ?? '') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Giá hiện hành</label>
            <input type="number" min="0" class="form-control" name="price_amount" value="{{ old('price_amount', $latestPrice->price_amount ?? '') }}" placeholder="Ví dụ 79000">
        </div>
        <div class="col-md-4">
            <label class="form-label">Áp cho rạp</label>
            <select class="form-select" name="cinema_id">
                <option value="">Mặc định rạp đầu tiên</option>
                @foreach($cinemas as $cinema)
                    <option value="{{ $cinema->id }}" @selected(old('cinema_id', $latestPrice->cinema_id ?? null) == $cinema->id)>{{ $cinema->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Hiệu lực từ</label>
            <input type="datetime-local" class="form-control" name="effective_from" value="{{ old('effective_from', optional($latestPrice?->effective_from)->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="col-md-3 form-check ms-3 mt-2">
            <input class="form-check-input" type="checkbox" name="is_combo" value="1" id="is_combo" @checked(old('is_combo', $product->is_combo))>
            <label class="form-check-label" for="is_combo">Là combo bán kèm</label>
        </div>
        <div class="col-md-3 form-check ms-3 mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->exists ? $product->is_active : true))>
            <label class="form-check-label" for="is_active">Đang kinh doanh</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary">Lưu sản phẩm</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-light-soft">Quay lại</a>
</div>
