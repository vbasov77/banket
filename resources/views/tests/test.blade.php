<form id="popupForm" action="/img_subj_store" method="post" enctype="multipart/form-data">

    @csrf <!-- Для Laravel (защита от CSRF) -->
    <div>
        <label for="file">Выберите файл:</label><br>
        <input type="file" id="file" name="img" required>
        <input type="hidden" name="id" value="5">
    </div>
    <button type="submit">Загрузить файл</button>

</form>