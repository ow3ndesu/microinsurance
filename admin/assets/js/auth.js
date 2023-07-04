document.getElementById('login').addEventListener('submit', (e) => {
    e.preventDefault();
    e.target.elements.submit.classList.add("d-flex", "justify-content-center");
    e.target.elements.submit.innerHTML = '<div class="spinner-border text-dark" role="status" id="login-loader"></div>';

    const username = e.target.elements.username.value;
    const password = e.target.elements.password.value;

    fetch('../routes/auth.route.php', {
        method: 'POST',
        body: JSON.stringify({
            action: "LoginAuth",
            username: username,
            password: password
        })
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.STATUS == 'UNSUCCESSFUL') {
                Swal.fire({
                    title: 'Something went wrong!',
                    icon: 'error',
                    text: data.MESSAGE
                });

                e.target.elements.submit.classList.remove("d-flex", "justify-content-center");
                e.target.elements.submit.innerHTML = '<span class="button__text" id="middle">Authenticated</span><i class="button__icon fas fa-xmark"></i>';
                return;
            }

            e.target.elements.submit.classList.remove("d-flex", "justify-content-center");
            e.target.elements.submit.innerHTML = '<span class="button__text" id="middle">Authenticated</span><i class="button__icon fas fa-check"></i>';

            Swal.fire({
                icon: 'success',
                text: 'Login Successful!',
                allowOutsideClick: false,
                confirmButtonColor: "#435ebe",
            }).then(() => {
                window.location.href = data.URL;
            })

        })
        .catch((err) => {
            Swal.fire({
                title: 'Something went wrong!',
                text: 'Please contact developers.',
                icon: 'error'
            });

            e.target.elements.submit.classList.remove("d-flex", "justify-content-center");
            e.target.elements.submit.innerHTML = '<span class="button__text" id="middle">Authenticate Credentials</span><i class="button__icon fas fa-xmark"></i>';
            console.log(err);
        });
});