SELECT
	u.personaname as 'User Name',
	u.likes_balance as 'likes_balance',
    CONCAT(u.wallet_balance, '$') as 'wallet_balance',
    u.wallet_total_refilled as 'wallet_total_refilled',
    totalLikes.countLikes as 'countLikes'
FROM user u,
LATERAL(
	select sum(amount) as countLikes
    from analytics a
    where a.user_id = u.id and a.object = 'boosterpack'
) totalLikes
WHERE u.id = 2;


SELECT
	HOUR(a.time_created) as 'time',
	CONCAT('Boosterpack №', a.object_id) as 'name',
	sum(b.price) as 'boosterTotalSum',
    CONCAT(sum(a.amount), '$') as 'userProfit'
from analytics a
left join boosterpack b on b.id = a.object_id
where a.object = 'boosterpack' and a.time_created > NOW() - INTERVAL 30 DAY

GROUP BY object_id, HOUR(a.time_created);


SELECT
	u.personaname as 'User Name',
	HOUR(a.time_created) as 'time',
	CONCAT('Boosterpack №', a.object_id) as 'name',
	sum(b.price) as 'boosterTotalSum',
    CONCAT(sum(a.amount), '$') as 'userProfit',
    CONCAT(u.wallet_balance, '$') as 'wallet_balance',
    u.likes_balance as 'likes_balance',
    u.wallet_total_refilled as 'wallet_total_refilled',
    totalLikes.countLikes as 'countLikes'
from analytics a
left join boosterpack b on b.id = a.object_id
left join user u on u.id = a.user_id,
LATERAL(
	select sum(amount) as countLikes
    from analytics a
    where a.user_id = u.id and a.object = 'boosterpack'
) totalLikes
where a.object = 'boosterpack' and a.time_created > NOW() - INTERVAL 30 DAY

GROUP BY object_id, HOUR(a.time_created), u.likes_balance, totalLikes.countLikes, u.wallet_total_refilled, u.personaname, wallet_balance;