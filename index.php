<?php
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

$classes = [
    1 => [
        "nome" => "Guerreiro da Luz Solar",
        "vida" => 120,
        "ataque" => 15,
        "emoji" => "🗡️"
    ],
    2 => [
        "nome" => "Maga da Lua Cheia",
        "vida" => 90,
        "ataque" => 20,
        "emoji" => "🔮"
    ],
    3 => [
        "nome" => "Arqueiro das Estrelas Cadentes",
        "vida" => 100,
        "ataque" => 18,
        "emoji" => "🏹"
    ]
];

$dificuldades = [
    1 => ["nome" => "Fácil", "multiplicador" => 1.0],
    2 => ["nome" => "Médio", "multiplicador" => 0.85],
    3 => ["nome" => "Difícil", "multiplicador" => 0.75],
];

function calcularAtaque($classe, $item, $classes) {
    $dano = $classes[$classe]["ataque"];

    if ($item === "ESPADA10") {
        $dano = $dano + 10;
    }
    elseif ($item === "CAJADO15") {
        $dano = $dano + 12;
    }
    elseif ($item === "ARCO05") {
        $dano = $dano + 15;
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

function execultarCombate($classe, $dano_final, $dano_base_classe, $vida_atual) {
    $log = [];
    $vida_inimigo = 50;
    $turno = 1;

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

            $turno_inimigo = execultarTurnoInimigo();

            if ($turno_inimigo["dano_causado"] > 0) {
                $vida_atual -= $turno_inimigo["dano_causado"];
                if ($vida_atual < 0) { $vida_atual = 0;}
                $log[] = "O inimigo usou " . $turno_inimigo["acao"] . " (dado: " . $turno_inimigo["dado"] . ") - causou " . $turno_inimigo["dano_causado"] . " de dano. Sua vida: " . $vida_atual;
            } else {
                $log[] = "Inimigo esquivou com Exito! (dado: " . $turno_inimigo["dado"] . ")";
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
    
    $nome = trim($_POST['nome'] ?? '');
    if (empty($nome)) {
        $erros[] = "Por favor, insira um nome para seu personagem.";
    }

    $classe = intval($_POST['classe'] ?? 0);
    if ($classe < 1 || $classe > 3) {
        $erros[] = "Escolha uma classe válida (1, 2 ou 3).";
    }

    $dificuldade = intval ($_POST['dificuldade'] ?? 0);
    if ($dificuldade < 1 || $dificuldade > 3) {
        $erros[] = "Escolha uma dificuldade válida (1, 2 ou 3).";
    }

    $item = strtoupper(trim($_POST['item'] ?? ''));
    if (empty($erros)) {
        $dados_validos = true;

        $vida_base = $classes[$classe]["vida"];

        $multiplicador = $dificuldades[$dificuldade]["multiplicador"];
        
        $vida_inicial = floor($vida_base * $multiplicador);

        $vida_atual = $vida_inicial;

        $dano_final = calcularAtaque($classe, $item, $classes);

        $dano_base_classe = $classes[$classe]["ataque"];

        $combate = execultarCombate($classe, $dano_final, $dano_base_classe, $vida_atual);

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

        <p>
            <?php echo $classes[$classe]["emoji"]; ?>
            bem vindo, <strong><?php echo htmlspecialchars($nome); ?></strong>!
        </p>
        
        <p>
            Classe: <strong><?php echo $classes[$classe]["nome"]; ?></strong><br>
            - Vida base: <?php echo $vida_base; ?><br>
            - Ataque base: <?php echo $classes[$classe]["ataque"]; ?>
        </p>

        <p>
            Dificuldade: <strong><?php echo $dificuldades[$dificuldade]["nome"]; ?></strong>
            (Multiplicador de vida: <?php echo ($multiplicador * 100); ?>%)
        </p>

        <p>
            ❤️ Vida inicial calculada:
            <strong><?php echo $vida_base; ?> x <?php echo ($multiplicador * 100); ?>%
            = <?php echo $vida_inicial; ?></strong>
        </p>

        <p>
            ⚔️ Dano por ataque: <strong><?php echo $dano_final; ?></strong>
            <?php if ($item !== ''): ?>
                (inclui bônus do item "<?php echo htmlspecialchars($item); ?>")
            <?php endif; ?>
        </p>

        <?php if (!empty($combate)): ?>

        <h2> ⚔️ Log de Combate ⚔️ </h2>

        <?php foreach ($combate["log"] as $linha): ?>
            <p><?php echo $linha; ?></p>
        <?php endforeach; ?>

        <?php if ($combate["resultado"] === "Vitoria"): ?>
            <h2>🏆 Você venceu! Vida restante: <?php echo $combate["vida_restante"]; ?></h2>
            <?php else: ?>
                <h2>💀 Você foi derrotado!</h2>
        <?php endif; ?>
    <?php endif; ?>

    <?php else: ?>

        <?php if (!empty($erros) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div style="color: red; border: 1px solid red; padding: 10px;">
                <strong>Corrija os erros abaixo:</strong>
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

            <label>Codigo do Item especial (ex: SWORD, SHIELD, STAFF):
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