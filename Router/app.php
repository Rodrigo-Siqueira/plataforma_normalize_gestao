<?php

use Pecee\SimpleRouter\SimpleRouter as Router;

Router::setDefaultNamespace('App\Controller');

// ==========================================
// ROTAS DE ERROS
// ==========================================
Router::get('/404', 'ErroController@erro404');

// ==========================================
// ROTAS DA DASHBOARD
// ==========================================
Router::get('/', 'DashboardController@mostrar');
Router::get('dashboard/', 'DashboardController@mostrar');

// ==========================================
// ROTAS DE PSICOLOGOS
// ==========================================
Router::get('psicologos/listar', 'PsicologoController@listar');
Router::get('psicologos/excluir/{id}', 'PsicologoController@excluir');

// Cadastro
Router::get('psicologos/cadastrar', 'PsicologoController@exibirFormularioCadastro');
Router::post('psicologos/cadastrar', 'PsicologoController@processarCadastro');

// Edição
Router::get('psicologos/editar/{id}', 'PsicologoController@exibirFormularioEdicao');
Router::post('psicologos/editar/{id}', 'PsicologoController@processarEdicao');


// ==========================================
// ROTAS DE PACIENTES
// ==========================================
Router::get('pacientes/listar/', 'PacienteController@listar');
Router::get('pacientes/excluir/{id}', 'PacienteController@excluir');

// Cadastro
Router::get('pacientes/cadastrar', 'PacienteController@exibirFormularioCadastro');
Router::post('pacientes/cadastrar', 'PacienteController@processarCadastro');

// Edição
Router::get('pacientes/editar/{id}', 'PacienteController@exibirFormularioEdicao');
Router::post('pacientes/editar/{id}', 'PacienteController@processarEdicao');

// ==========================================
// ROTAS DE PRODUTOS
// ==========================================
Router::get('produtos/listar/', 'ProdutoController@listar');
Router::get('produtos/excluir/{id}', 'ProdutoController@excluir');

// Cadastro
Router::get('produtos/cadastrar', 'ProdutoController@exibirFormularioCadastro');
Router::post('produtos/cadastrar', 'ProdutoController@processarCadastro');

// Edição
Router::get('produtos/editar/{id}', 'ProdutoController@exibirFormularioEdicao');
Router::post('produtos/editar/{id}', 'ProdutoController@processarEdicao');

// ==========================================
// ROTAS DE VENDAS
// ==========================================
Router::get('vendas/listar/', 'VendaController@listar');

// ==========================================
// ROTAS DE SESSÕES
// ==========================================
Router::get('sessoes/listar/', 'SessaoController@listar');

// ==========================================
// ROTAS DE REPASSES
// ==========================================
Router::get('repasses/listar/', 'RepasseController@listar');

// ==========================================
// ROTAS DE DESPESAS
// ==========================================
Router::get('despesas/listar/', 'DespesaController@listar');

// ==========================================
// ROTAS DE FLUXO DE CAIXA
// ==========================================
Router::get('fluxocaixa/listar/', 'FluxoCaixaController@listar');
