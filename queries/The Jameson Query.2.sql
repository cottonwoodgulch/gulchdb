SELECT t.title, c.first_name, c.primary_name, deg.degree, a.street_address_1, a.street_address_2, a.city, a.state, a.country, a.postal_code, p.number, f.fund, d.date, d.amount, sum( d2.amount )  AS fy_2003, at.rank, min( at.rank )  AS address_rank, pt.rank, min( pt.rank )  AS phone_rankFROM contacts AS cLEFT  JOIN titles AS t ON t.title_id = c.title_idLEFT  JOIN degrees AS deg ON deg.degree_id = c.degree_idJOIN donation_associations AS da ON da.contact_id = c.contact_idJOIN donations AS d ON d.donation_id = da.donation_id AND d.date >=  '2002-10-1' AND d.date <=  '2003-10-1'JOIN funds AS f ON f.fund_id = d.fund_idLEFT  JOIN donation_associations AS da2 ON da2.contact_id = c.contact_idLEFT  JOIN donations AS d2 ON d2.donation_id = da2.donation_id AND d2.date >=  '2003-10-1'LEFT  JOIN address_associations AS aa ON aa.contact_id = c.contact_idLEFT  JOIN addresses AS a ON a.address_id = aa.address_idLEFT  JOIN address_types AS at ON at.address_type_id = a.address_type_idLEFT  JOIN phone_associations AS pa ON pa.contact_id = c.contact_idLEFT  JOIN phones AS p ON p.phone_id = pa.phone_idLEFT  JOIN phone_types AS pt ON pt.phone_type_id = p.phone_type_idGROUP  BY d.donation_idHAVING fy_2003 IS  NULL  AND address_rank = at.rank AND phone_rank = pt.rankORDER  BY c.primary_name ASC , c.first_name ASC , deg.degree DESC;