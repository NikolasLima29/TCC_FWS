<!doctype html>
<html lang="en">

<head>
    <title>Página de Dados</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="../CSS/carrinho.css">
</head>

<body>
    <!-- Cabeçalho -->
    <header id="header">
        <!-- Logo -->
        <div class="logo">
            <a href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <!-- Menu -->
        <nav>
            <ul class="ul">
                <li><a href="../../produto/HTML/produto.html">Produtos</a></li>
                <li><a href="../../tela_sobre_nos/HTML/sobre_nos.html">Sobre nós</a></li>
            </ul>
        </nav>

        <!-- Carrinho -->
        <div class="carrinho">
            <a href="#">
                <img src="../../index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
            </a>
        </div>
    </header>

    <!-- Carrinho de compras -->
    <div class="container py-5">
        <h1 class="mb-5">Seu carrinho</h1>
        <div class="row">
            <div class="col-lg-8">
                <!-- Itens do carrinho -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row cart-item mb-3">
                            <div class="col-md-3">
                                <img src="../IMG/guarana.png" alt="1° Produto" class="img-fluid rounded" width="50%"
                                    height="50%">
                            </div>
                            <div class="col-md-5">
                                <h5 class="card-title">Guaraná Antarctica</h5>
                                <p class="text-muted">Categoria: Bebidas</p>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                    <input style="max-width:100px" type="text"
                                        class="form-control  form-control-sm text-center quantity-input" value="1">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <p class="fw-bold">R$ 5,00</p>
                                <button class="btn btn-sm btn-outline-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-trash" viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="row cart-item mb-3">
                            <div class="col-md-3">
                                <img src="../IMG/guarana.png" alt="1° Produto" class="img-fluid rounded" width="50%"
                                    height="50%">
                            </div>
                            <div class="col-md-5">
                                <h5 class="card-title">Guaraná Antarctica</h5>
                                <p class="text-muted">Categoria: Bebidas</p>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">-</button>
                                    <input style="max-width:100px" type="text"
                                        class="form-control  form-control-sm text-center quantity-input" value="1">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <p class="fw-bold">R$ 5,00</p>
                                <button class="btn btn-sm btn-outline-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-trash" viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Sumário do pedido -->
                <div class="card cart-summary">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Sumário do pedido</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span>R$ 10,00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Desconto</span>
                            <span>R$ 0,00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong>R$ 10,00</strong>
                        </div>
                        <button class="btn btn-primary w-100">Reservar</button>
                        <hr>
                        <!-- Tempo de reserva -->
                        <select class="form-select form-select-sm" aria-label=".form-select-sm example">
                            <option selected>Selecione o tempo de reserva</option>
                            <option value="1">1 hora</option>
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                        </select>
                    </div>
                </div>
                <!-- Código de promoção -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Aplicar código de promoção</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Escreva o código de promoção">
                            <button class="btn btn-outline-secondary" type="button">Aplicar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main></main>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
    </script>
</body>

</html>