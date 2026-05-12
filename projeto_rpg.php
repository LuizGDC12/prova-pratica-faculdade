 Prova pratica de PHP e HTML - 2026 <br>
 Professor: Felipe Costa Fernandes <br>
 Desenvolvido por: <br>
 Luiz Gustavo Domingos Campos <br>
 Victor Hugo Alvarenga Costa <br>
 Vitor Hugo Moreira França <br>
 Kalebe Petali Bernardino <br>


<?php
session_start();
$erros = [];
$dados_validos = false;
$nome = '';
$classe = 0;
$dificuldade = 0;
$item = '';
$vida_base = 0;
$multiplicador = 1.0;
$vida_inicial = 0;
$vida_atual = 0;
$dano_final = 0;
$resultado_turno = [];
$dano_base_classe = 0;
$combate = [];
$experiencia = 0;
$fase_atual = 1;
$game_over = false;
$vitoria_final = false;

if (isset($_SESSION['fase_atual'])) {
    $fase_atual = $_SESSION['fase_atual'];
}

if (isset($_SESSION['vida_atual'])) {
    $vida_atual = $_SESSION['vida_atual'];
}

if (isset($_SESSION['nome'])) {
    $nome = $_SESSION['nome'];
}

if (isset($_SESSION['classe'])) {
    $classe = $_SESSION['classe'];
}

if (isset($_SESSION['dificuldade'])) {
    $dificuldade = $_SESSION['dificuldade'];
}

if (isset($_SESSION['item'])) {
    $item = $_SESSION['item'];
}

if (isset($_SESSION['experiencia'])) {
    $experiencia = $_SESSION['experiencia'];
}

$classes = [
    1 => [
        "nome" => "Guerreiro da Luz Solar",
        "vida" => 100,
        "ataque" => 15,
        "emoji" => "🗡️"
    ],
    2 => [
        "nome" => "Maga da Lua Cheia",
        "vida" => 70,
        "ataque" => 20,
        "emoji" => "🔮"
    ],
    3 => [
        "nome" => "Arqueiro das Estrelas Cadentes",
        "vida" => 80,
        "ataque" => 18,
        "emoji" => "🏹"
    ]
];

$dificuldades = [
    1 => ["nome" => "Fácil", "multiplicador" => 1.0],
    2 => ["nome" => "Médio", "multiplicador" => 0.80],
    3 => ["nome" => "Difícil", "multiplicador" => 0.50],
];

$inimigos = [
    1 => ["nome" => "Goblin em furia",       "emoji" => "🧌", "vida" => 30,  "ataque" => 4],
    2 => ["nome" => "Dragão de Fogo",          "emoji" => "🐲", "vida" => 40,  "ataque" => 8],
    3 => ["nome" => "Olho malefico",           "emoji" => "👁️", "vida" => 50, "ataque" => 9],
    4 => ["nome" => "Cavaleiro das Trevas",   "emoji" => "🥷", "vida" => 60, "ataque" => 10],
    5 => ["nome" => "Keffler, o Rei Demônio", "emoji" => "👹", "vida" => 100, "ataque" => 15],
];

function calcularAtaque($classe, $item, $classes) {
    $dano = $classes[$classe]["ataque"];

    if ($item === "ESPADA10") {
        $dano = $dano + 10;
    }
    elseif ($item === "CAJADO20") {
        $dano = $dano + 20;
    }
    elseif ($item === "ARCO05") {
        $dano = $dano + 5;
    }
    return $dano;
}

function execultarTurnoJogador($classe, $dano_final, $dano_base_classe) {
    $dado = rand(1, 10);
    $dano_causado = 0;
    $acao = '';

    if ($classe == 1) {
        if ($dado >= 1 && $dado <= 5) {
            $dano_causado = 10;
            $acao = "Ataque normal";
        } elseif ($dado >= 6 && $dado <= 7) {
            $dano_causado = 0;
            $acao = "Esquiva";
        } else {
            $dano_causado = 20;
            $acao = "Ataque Pesado";
        }
    } elseif ($classe == 2) {
        if ($dado >= 1 && $dado <= 5) {
            $dano_causado = 12;
            $acao = "Ataque normal";
        } elseif ($dado >= 6 && $dado <= 8) {
            $dano_causado = 0;
            $acao = "Esquiva";
        } else {
            $dano_causado = 28;
            $acao = "Ataque Pesado";
        }
    } elseif ($classe == 3) {
        if ($dado >= 1 && $dado <= 6) {
            $dano_causado = 15;
            $acao = "Ataque normal";
        } elseif ($dado >= 7 && $dado <= 9) {
            $dano_causado = 0;
            $acao = "Esquiva";
        } else {
            $dano_causado = 30;
            $acao = "Ataque Pesado";
        }
    }

    if ($dano_causado > 0) {
        $bonus_item = $dano_final - $dano_base_classe;
        $dano_causado = $dano_causado + $bonus_item;
    }

    return [
        "dado"         => $dado,
        "acao"         => $acao,
        "dano_causado" => $dano_causado
    ];
}

function execultarTurnoInimigo() {
    $dado = rand (1, 10);
    $dado_causado = 0;
    $acao = '';

    if ($dado >= 1 && $dado <= 5) {
        $dado_causado = 10;
        $acao = "Ataque normal";

    } elseif ($dado == 6) {
        $dado_causado = 0;
        $acao = "Esquiva";

    } elseif ($dado >= 7 && $dado <= 9) {
        $dado_causado = 12;
        $acao = "Ataque Pesado";
    }

    return [
        "dado" => $dado,
        "acao" => $acao,
        "dano_causado" => $dado_causado
    ];
}

function execultarCombate($classe, $dano_final, $dano_base_classe, $vida_atual, $inimigo) {
    $log = [];
    $vida_inimigo = $inimigo['vida'];
    $turno = 1;

    $log[] = "⚠️ Você encontrou " . $inimigo["emoji"] . " " . $inimigo["nome"] . "! Prepare-se para batalhar!";

    while ($vida_atual > 0 && $vida_inimigo > 0) {
        $log[] = "--- Turno $turno ---";

        $turno_jogador = execultarTurnoJogador($classe, $dano_final, $dano_base_classe);

        if ($turno_jogador["dano_causado"] > 0) {
            $vida_inimigo -= $turno_jogador["dano_causado"];

            if ($vida_inimigo < 0) {
                $vida_inimigo = 0;
            }
            $log[] =" Voce usou " . $turno_jogador["acao"] . " (dado: " . $turno_jogador["dado"] . ") - causou " . $turno_jogador["dano_causado"] . " de dano. Vida do inimigo:" . $vida_inimigo;
        } else {
            $log[] = "Voce esquivou com Exito! (dado: " . $turno_jogador["dado"] . ")";
        }

        if ($vida_inimigo <= 0) {
                break;
        }
        if ($turno_jogador["acao"] !== "Esquiva") {
            $turno_inimigo = execultarTurnoInimigo();


            if ($turno_inimigo["dano_causado"] > 0) {
                $vida_atual -= $turno_inimigo["dano_causado"];
                if ($vida_atual < 0) { $vida_atual = 0;}
                $log[] = "O inimigo usou " . $turno_inimigo["acao"] . " (dado: " . $turno_inimigo["dado"] . ") - causou " . $turno_inimigo["dano_causado"] . " de dano. Sua vida: " . $vida_atual;
            } else {
                $log[] = "Inimigo esquivou com Êxito! (dado: " . $turno_inimigo["dado"] . ")";
            }
        } else {
            $turno_inimigo_frustado = execultarTurnoInimigo();
            if ($turno_inimigo_frustado["dano_causado"] > 0) {
                $log[] = " O inimigo usou " . $turno_inimigo_frustado["acao"] . " (dado: " . $turno_inimigo_frustado["dado"] . ") e tentou causar " . $turno_inimigo_frustado["dano_causado"] . " pontos de dano em voce";
            }
            $log[] = "🛡️O inimigo tropeçou e errou o ataque!";
        }

            $turno++;
        }

        if ($vida_atual > 0) {
            $resultado = "Vitoria";
        } else {
            $resultado = "Derrota";
        }

        return [
            "log" => $log,
            "resultado" => $resultado,
            "vida_restante" => $vida_atual
        ];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['reiniciar'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['avancar'])) {
        $dados_validos = true;
        $nome             = $_SESSION['nome'];
        $classe           = $_SESSION['classe'];
        $dificuldade      = $_SESSION['dificuldade'];
        $item             = $_SESSION['item'];
        $dano_final       = $_SESSION['dano_final'];
        $dano_base_classe = $_SESSION['dano_base_classe'];
        $vida_atual       = $_SESSION['vida_atual'];
        $fase_atual       = $_SESSION['fase_atual'];
        $experiencia      = $_SESSION['experiencia'];

        if ($fase_atual > 5) {
            $fase_atual = 5;
            $_SESSION['fase_atual'] = 5;
        }

        $inimigo_atual = $inimigos[$fase_atual];
        $combate = execultarCombate($classe, $dano_final, $dano_base_classe, $vida_atual, $inimigo_atual);

        $_SESSION['dano_final']       = $dano_final;
        $_SESSION['dano_base_classe'] = $dano_base_classe;

        if ($combate["resultado"] === "Vitoria") {
            $experiencia += $dificuldade;
            $_SESSION['experiencia'] = $experiencia;
            if ($fase_atual == 5) {
                $vitoria_final = true;
                session_destroy();
            } else {
                $_SESSION['fase_atual'] = $fase_atual + 1;
                $_SESSION['vida_atual'] = $combate["vida_restante"];
            }
        } else {
            $game_over = true;
            session_destroy();
        }

    } elseif (!isset($_POST['reiniciar'])) {

        $nome = trim($_POST['nome'] ?? '');
        if (empty($nome)) {
            $erros[] = "Por favor, insira um nome para seu personagem.";
        }

        $classe = intval($_POST['classe'] ?? 0);
        if ($classe < 1 || $classe > 3) {
            $erros[] = "Escolha uma classe válida (1, 2 ou 3).";
        }

        $dificuldade = intval($_POST['dificuldade'] ?? 0);
        if ($dificuldade < 1 || $dificuldade > 3) {
            $erros[] = "Escolha uma dificuldade válida (1, 2 ou 3).";
        }

        $item = strtoupper(trim($_POST['item'] ?? ''));

        if (empty($erros)) {
            $dados_validos = true;

            $experiencia = 0;
            for ($i = 0; $i < $dificuldade; $i++) {
                $experiencia += 1;
            }

            $vida_base        = $classes[$classe]["vida"];
            $multiplicador    = $dificuldades[$dificuldade]["multiplicador"];
            $vida_inicial     = floor($vida_base * $multiplicador);
            $vida_atual       = $vida_inicial;
            $dano_final       = calcularAtaque($classe, $item, $classes);
            $dano_base_classe = $classes[$classe]["ataque"];

            $_SESSION['experiencia'] = $experiencia;

            $fase_atual = 1;
            $_SESSION['fase_atual'] = 1;

            $inimigo_atual = $inimigos[$fase_atual];
            $combate = execultarCombate($classe, $dano_final, $dano_base_classe, $vida_atual, $inimigo_atual);

            $_SESSION['nome']             = $nome;
            $_SESSION['classe']           = $classe;
            $_SESSION['dificuldade']      = $dificuldade;
            $_SESSION['item']             = $item;
            $_SESSION['dano_final']       = $dano_final;
            $_SESSION['dano_base_classe'] = $dano_base_classe;

            if ($combate["resultado"] === "Vitoria") {
                $_SESSION['fase_atual'] = 2;
                $_SESSION['vida_atual'] = $combate["vida_restante"];
            } else {
                $game_over = true;
                session_destroy();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPG de turnos</title>
</head>
<body>
    <center>
        <h1>⚔️ RPG de Turnos ⚔️</h1>

    <?php if ($dados_validos): ?>

        <h2>📜 FICHA DO HERÓI</h2>

        <p>
            <?php echo $classes[$classe]["emoji"]; ?>
            <strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?>
        </p>

        <p>
            <strong>Classe:</strong> <?php echo $classes[$classe]["nome"]; ?>
        </p>

        <p>
            <strong>Vida Final:</strong> <?php echo $vida_inicial; ?> pts
            (calculo: <?php echo $vida_base; ?> base
            <?php if ($multiplicador < 1): ?>
                <?php echo (1 - $multiplicador) * 100; ?>%
                da dificuldade <?php echo $dificuldades[$dificuldade]["nome"]; ?>
                <?php endif; ?>)
        </p>

        <p>
            <strong>Poder de Ataque:</strong> <?php echo $dano_final; ?> pts
            (calculo: <?php echo $classes[$classe]["ataque"]; ?> base da classe)
            <?php if ($item !== ''): ?>
                + bonus do item "<?php echo htmlspecialchars($item); ?>"
            <?php endif; ?>
        </p>

        <p>
            <strong>Experiencia Inicial:</strong> <?php echo $experiencia; ?> XP
            Dificuldade: <?php echo $dificuldades[$dificuldade]["nome"]; ?>
        </p>

        <p>
            Dificuldade: <strong><?php echo $dificuldades[$dificuldade]["nome"]; ?></strong>
            (Multiplicador de vida: <?php echo ($multiplicador * 100); ?>%)
        </p>

        <?php if (!empty($combate)): ?>

        <h2> ⚔️ Log de Combate ⚔️ </h2>

        <?php foreach ($combate["log"] as $linha): ?>
            <p><?php echo $linha; ?></p>
        <?php endforeach; ?>

        <?php if ($vitoria_final): ?>
    <h2>🌟 VOCÊ SALVOU O MUNDO! 🌟</h2>
    <p>O terrível <strong>Keffler, o Rei Demônio</strong> foi derrotado!</p>
    <p>Graças à coragem de <strong><?php echo htmlspecialchars($nome); ?></strong>, a paz foi restaurada.</p>
    <br>
    <p><em>Obrigado por jogar!</em></p>
    <form action="" method="post">
        <input type="hidden" name="reiniciar" value="1">
        <button type="submit">🏠 Voltar ao Menu</button>
    </form>

<?php elseif ($combate["resultado"] === "Vitoria"): ?>
    <h2>🏆 Você venceu a Fase <?php echo $fase_atual; ?>! Vida restante: <?php echo $combate["vida_restante"]; ?></h2>
    <p>Prepare-se para o próximo desafio...</p>
    <form action="" method="post">
        <input type="hidden" name="avancar" value="1">
        <button type="submit">⚔️ Avançar na jornada <?php echo isset($_SESSION['fase_atual']) ? $_SESSION['fase_atual'] : ''; ?>!</button>
    </form>

<?php else: ?>
    <h2>💀 Você foi derrotado!</h2>
    <p>Sua jornada e a vida no planeta terminou aqui, <strong><?php echo htmlspecialchars($nome); ?></strong>...</p>
    <form action="" method="post">
        <input type="hidden" name="reiniciar" value="1">
        <button type="submit">🔄 Jogar Novamente</button>
    </form>
<?php endif; ?>

    <?php endif; ?>

    <?php else: ?>

        <?php if (!empty($erros) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div style="color: red; border: 1px solid red; padding: 10px;">
                <strong>Preencha todos os campos corretamente:</strong>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?php echo $erro; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action ="" method="post">

            <label>Nome do Personagem:
                <input type="text" name="nome"
                       value="<?php echo htmlspecialchars($nome); ?>">
            </label><br><br>

            <label>Classe:
                <select name="classe">
                    <option value="0">-- Escolha --</option>
                    <option value="1" <?php if ($classe == 1) { echo 'selected';} ?>>
                        1 - Guerreiro da Luz Solar
                    </option>
                    <option value="2" <?php if ($classe == 2) { echo 'selected';} ?>>
                        2 - Maga da Lua Cheia
                    </option>
                    <option value="3" <?php if ($classe == 3) { echo 'selected';} ?>>
                        3 - Arqueiro das Estrelas Cadentes
                    </option>
                </select>
            </label><br><br>

            <label>Dificuldade:
                <select name="dificuldade">
                    <option value="0">-- Escolha --</option>
                    <option value="1" <?php if ($dificuldade == 1) { echo 'selected';} ?>>
                        1 - Fácil
                    </option>
                    <option value="2" <?php if ($dificuldade == 2) { echo 'selected';} ?>>
                        2 - Médio
                    </option>
                    <option value="3" <?php if ($dificuldade == 3) { echo 'selected';} ?>>
                        3 - Difícil
                    </option>
                </select>
            </label><br><br>

            <label>Codigo do Item especial (ex: ESPADA10, CAJADO20 ou ARCO05):
                <input type="text" name="item"
                       placeholder="Ex: ESPADA10"
                       value="<?php echo htmlspecialchars($item); ?>">
            </label><br><br>

            <button type="submit">⚔️ Jogar!</button>

        </form>

        <?php endif; ?>
    </center>
</body>
</html>