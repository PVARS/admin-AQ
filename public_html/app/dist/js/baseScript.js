function sweetConfirm(type,message,callback) {
    var title = '';
    switch (type)
    {
        case 1 : {
            title = 'XOÁ';
            break;
        }
        default : {
            break;
        }
    }
    Swal.fire({
        title: title,
        html: message,
        icon: 'warning',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Huỷ',
        showCancelButton: true,
        showCloseButton: true
    }).then(function (confirmed) {
        return callback(confirmed && confirmed.value == true);
    });
};