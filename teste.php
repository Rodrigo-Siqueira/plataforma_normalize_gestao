<?php

// --- EXEMPLO: se não houver dados vindos do seu controller, cria dados de exemplo:
if (!isset($paciente)) {
    $paciente = [
        'id' => 123,
        'nome_completo' => 'João da Silva',
        'contato' => '(11) 99999-9999',
        'responsavel' => 'Maria da Silva'
    ];
}

if (!isset($vendas)) {
    // cada venda: id, produto_nome, numero_sessoes, valor_cobrado, status_pagamento
    $vendas = [
        ['id'=>1,'produto_nome'=>'Pacote 4 sessões','numero_sessoes'=>4,'valor_cobrado'=>'280.00','status_pagamento'=>'PAGO'],
        ['id'=>2,'produto_nome'=>'Sessão avulsa','numero_sessoes'=>1,'valor_cobrado'=>'80.00','status_pagamento'=>'PAGO']
    ];
}

if (!isset($sessoes)) {
    // sessoes devem vir ordenadas por venda_origem_id, data_agendada, hora_agendada
    // cada sessão: id, venda_origem_id, psicologo, data_agendada (Y-m-d), hora_agendada (H:i), status
    $sessoes = [
        ['id'=>101,'venda_origem_id'=>1,'psicologo'=>'Dra. Ana Souza','data_agendada'=>'2025-09-02','hora_agendada'=>'10:00','status'=>'REALIZADA','numero_sessoes'=>4],
        ['id'=>102,'venda_origem_id'=>1,'psicologo'=>'Dra. Ana Souza','data_agendada'=>'2025-09-05','hora_agendada'=>'14:00','status'=>'REALIZADA','numero_sessoes'=>4],
        ['id'=>103,'venda_origem_id'=>1,'psicologo'=>'Dra. Ana Souza','data_agendada'=>'2025-09-10','hora_agendada'=>'09:00','status'=>'AGENDADA','numero_sessoes'=>4],
        ['id'=>104,'venda_origem_id'=>1,'psicologo'=>'Dra. Ana Souza','data_agendada'=>'2025-09-15','hora_agendada'=>'11:00','status'=>'AGENDADA','numero_sessoes'=>4],
        ['id'=>201,'venda_origem_id'=>2,'psicologo'=>'Dr. Pedro','data_agendada'=>'2025-09-20','hora_agendada'=>'16:00','status'=>'AGENDADA','numero_sessoes'=>1],
    ];
}

// --- Calcula saldo por venda e carimbo dinâmico
// Presume que $sessoes vem ordenado por venda_origem_id, data_agendada, hora_agendada
$contadorPorVenda = []; // venda_id => current count
$carimbos = []; // sessao_id => "n/m"

foreach ($sessoes as $sessao) {
    $vid = $sessao['venda_origem_id'];
    $total = isset($sessao['numero_sessoes']) ? intval($sessao['numero_sessoes']) : 1;

    if (!isset($contadorPorVenda[$vid])) $contadorPorVenda[$vid] = 0;
    $contadorPorVenda[$vid]++;
    $carimbos[$sessao['id']] = $contadorPorVenda[$vid] . '/' . $total;
}

// --- Calcula saldo total (ex.: quantas usadas x total disponível)
// Soma numero_sessoes de vendas pagas e conta sessoes com status consumidoras (AGENDADA, REALIZADA, REMARCADA)
$totalCreditos = 0;
foreach ($vendas as $v) {
    if ($v['status_pagamento'] === 'PAGO') {
        $totalCreditos += intval($v['numero_sessoes']);
    }
}
$totalDebitos = 0;
foreach ($sessoes as $s) {
    if (in_array($s['status'], ['AGENDADA','REALIZADA','REMARCADA'])) $totalDebitos++;
}
$saldoDisponivel = $totalCreditos - $totalDebitos;
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Perfil do Paciente — <?= htmlspecialchars($paciente['nome_completo']) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <style>
    .card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .small-muted { font-size:13px; color:#666; }
    .pill { display:inline-block; padding:6px 10px; border-radius:999px; font-weight:600; }
  </style>
</head>
<body class="w3-light-grey">

<div class="w3-container w3-padding-large">
  <div class="w3-card w3-white w3-round w3-padding">
    <div class="card-header">
      <div>
        <h2 class="w3-margin-0"><?= htmlspecialchars($paciente['nome_completo']) ?></h2>
        <div class="small-muted">
          Contato: <?= htmlspecialchars($paciente['contato']) ?> — Responsável: <?= htmlspecialchars($paciente['responsavel']) ?>
        </div>
      </div>
      <div style="text-align:right">
        <a href="paciente_edit.php?id=<?= urlencode($paciente['id']) ?>" class="w3-button w3-blue w3-round">Editar Paciente</a>
        <a href="sessao_form.php?paciente_id=<?= urlencode($paciente['id']) ?>" class="w3-button w3-green w3-round">Adicionar Sessão</a>
      </div>
    </div>

    <hr>

    <!-- VENDAS / PACOTES -->
    <h4>Pacotes / Vendas</h4>
    <table class="w3-table w3-striped w3-bordered w3-small">
      <thead>
        <tr class="w3-light-grey">
          <th>Produto</th>
          <th>Nº Sessões</th>
          <th>Valor</th>
          <th>Status Pagamento</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vendas as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['produto_nome']) ?></td>
          <td><?= intval($v['numero_sessoes']) ?></td>
          <td>R$ <?= number_format(floatval($v['valor_cobrado']),2,',','.') ?></td>
          <td>
            <?php if ($v['status_pagamento'] === 'PAGO'): ?>
              <span class="w3-tag w3-green">PAGO</span>
            <?php else: ?>
              <span class="w3-tag w3-orange"><?= htmlspecialchars($v['status_pagamento']) ?></span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <hr>

    <!-- SESSÕES -->
    <h4>Sessões</h4>
    <table class="w3-table w3-striped w3-bordered w3-small">
      <thead>
        <tr class="w3-light-grey">
          <th>Data</th>
          <th>Hora</th>
          <th>Psicólogo</th>
          <th>Status</th>
          <th>Carimbo</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sessoes as $s): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($s['data_agendada'])) ?></td>
            <td><?= date('H:i', strtotime($s['hora_agendada'])) ?></td>
            <td><?= htmlspecialchars($s['psicologo']) ?></td>
            <td>
              <?php
                $cls = 'w3-light-grey';
                if ($s['status'] === 'REALIZADA') $cls = 'w3-green';
                if (strpos($s['status'],'CANCELADA') === 0) $cls = 'w3-red';
                if ($s['status'] === 'REMARCADA') $cls = 'w3-blue';
              ?>
              <span class="w3-tag <?= $cls ?>"><?= htmlspecialchars($s['status']) ?></span>
            </td>
            <td><span class="pill w3-light-blue"><?= htmlspecialchars($carimbos[$s['id']] ?? '1/1') ?></span></td>
            <td>
              <a class="w3-button w3-small w3-border" href="sessao_form.php?action=edit&id=<?= $s['id'] ?>">Editar</a>
              <a class="w3-button w3-small w3-red" href="sessao_delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Excluir sessão?')">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="w3-margin-top">
      <strong>Saldo de sessões:</strong>
      <span class="w3-tag w3-white">Usadas: <?= $totalDebitos ?> / Disponíveis: <?= $totalCreditos ?></span>
      <span class="w3-margin-left small-muted">Saldo restante: <?= $saldoDisponivel >= 0 ? $saldoDisponivel : 0 ?></span>
    </div>

  </div>
</div>

</body>
</html>
