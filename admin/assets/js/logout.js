document.querySelector("#logoutbtn").addEventListener("click", (e) => {
    Swal.fire({
        title: "Are you sure you want to logout?",
        icon: 'question',
        showCancelButton: true,
        showLoaderOnConfirm: true,
        confirmButtonText: 'Logout',
        confirmButtonColor: "#435ebe",
        cancelButtonText: "Cancel",
        allowOutsideClick: false,
        customClass: {
            input: 'text-center',
        },
        preConfirm: (e) => {
            return fetch('../../routes/auth.route.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: "LogoutAuth",
                })
            })
            .then((response) => response.text())
            .then((data) => {
                if (!data || data !== 'LOGOUT_SUCCESS') {
                    Swal.showValidationMessage(
                        'Unable to logout, please reload.'
                    )
                }

                return data;
            })
            .catch((err) => {
                Swal.showValidationMessage(
                    'Something Went Wrong.'
                )
            })
        },
    }).then((result) => {
        if (result.value == "LOGOUT_SUCCESS") {
            window.location.href = "../../index"
        }
    });
});

function logOut() {
    Swal.fire({
        title: "Are you sure you want to logout?",
        icon: 'question',
        showCancelButton: true,
        showLoaderOnConfirm: true,
        confirmButtonText: 'Logout',
        confirmButtonColor: "#435ebe",
        cancelButtonText: "Cancel",
        allowOutsideClick: false,
        customClass: {
            input: 'text-center',
        },
        preConfirm: (e) => {
            return fetch('../../routes/auth.route.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: "LogoutAuth",
                })
            })
            .then((response) => response.text())
            .then((data) => {
                if (!data || data !== 'LOGOUT_SUCCESS') {
                    Swal.showValidationMessage(
                        'Unable to logout, please reload.'
                    )
                }

                return data;
            })
            .catch((err) => {
                Swal.showValidationMessage(
                    'Something Went Wrong.'
                )
            })
        },
    }).then((result) => {
        if (result.value == "LOGOUT_SUCCESS") {
            window.location.href = "../../index"
        }
    });
}