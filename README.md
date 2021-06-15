# push-notification
Remote Push Notification for Android and iOS

## Configuração no iOS 
### Fluxo do APNS (Apple Push Notification Service)
![alt text](https://git.codificar.com.br/laravel-libs/push-notification/raw/master/img/authtoken.png)

### É necessário configurar apenas no painel: 
- [Team ID](#team-id)
- [Arquivo p8](#arquivo-p8)
- [Key ID](#key-id)
- [Pacote do App Provider](#pacote-do-app-provider)
- [Pacote do App User](#pacote-do-app-user)

### Team ID
Para obter o team id, faça login no apple developer account, clique no menu "Certificates, Ids & Profiles" e pegue o team id no canto superior direito (veja na imagem abaixo)
![alt text](https://git.codificar.com.br/laravel-libs/push-notification/raw/master/img/team_id.png)


### Arquivo p8
Para obter o team o arquivo p8, clique no menu "Keys". Se já existir alguma chave, clique nela, se não existir, clique no simbolo "+". Selecione "Apple Push Notifications service (APNs)" e clique continuar. Faça o download do arquivo e importe no painel admin (menu chaves e push).
![alt text](https://git.codificar.com.br/laravel-libs/push-notification/raw/master/img/p8_file.png)

### Key ID
Para obter o Key Id é quase da mesma forma de obter o arquivo p8. Na página do arquivo p8, contém o Key Id:
![alt text](https://git.codificar.com.br/laravel-libs/push-notification/raw/master/img/key_id.png)

### Pacote do App Provider
Nome do pacote do app prestador. Deve ser o mesmo que está configurado no projeto da automação do app.

### Pacote do App User
Nome do pacote do app usuário. Deve ser o mesmo que está configurado no projeto da automação do app.

