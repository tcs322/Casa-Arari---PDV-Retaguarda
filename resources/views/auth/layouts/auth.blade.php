<!doctype html>
<html lang="en" id="htmlMainPage">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tw-elements/dist/css/tw-elements.min.css" />
    <script src="https://cdn.tailwindcss.com/3.3.0"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-mode="dark"]'],
            theme: {
            fontFamily: {
                sans: ["Roboto", "sans-serif"],
                body: ["Roboto", "sans-serif"],
                mono: ["ui-monospace", "monospace"],
            },
            },
            corePlugins: {
            preflight: false,
            },
        };
    </script>
    <title>Atenticação - {{config('app.name')}}</title>
</head>
<script>
    function lighMode() {
        console.log('executou');
        document.getElementById('htmlMainPage').classList.add('dark')
    }
</script>
<body class="antialiased">

{{-- @include('components.alerts.form-errors')
<form action="{{route('auth.login')}}" method="POST">
    @csrf
    <input type="email" name="email" required><br>
    <input type="password" name="password" required><br>
    <input type="submit" value="login">
</form> --}}

<!-- This is an example component -->
<div class="h-screen font-sans login bg-cover">
    <div class="container mx-auto h-full flex flex-1 justify-center items-center">
        <div class="w-full max-w-lg">
          <div class="leading-loose">
            @yield('content-auth')
          </div>
        </div>
    </div>
</div>

</body>
</html>
    <style>
      .login{

        background: url('https://tailwindadmin.netlify.app/dist/images/login-new.jpeg');

      /* background: url('http://bit.ly/2gPLxZ4'); */
      background-repeat: no-repeat;
      background-size: cover;
    }
    </style>
