<footer class="footer py-4  ">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="copyright text-center text-sm text-muted text-lg-start">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script>,
                    made with <i class="fa fa-heart" aria-hidden="true"></i> by
                    <a href="{{ env('APP_OWNERSHIP_URL') }}" class="text-white"
                        target="_blank">{{ env('APP_OWNERSHIP') }}</a>
                </div>
            </div>
        </div>
    </div>
</footer>
