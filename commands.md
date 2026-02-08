Act as senior dev,

I have this migration plan earlier, But  need to deliver fast . So do these steps :

- According to current setup, Only 1 owner exists, They create events/prmocodes, setup payment gateways and tracking Keys.
- So everything is linked for a single bussiness user.
- I have multiple roles (Spatie roles/permission) in the system, owner, admin, team-member, user described below :

    1) Owner has all permissions and all bookings will be made on owner ID + booker's ID.
    2) Admin and team-member , i have created but have not used them properly n admin panel, what they can use and what they cant.
    3) User is just who can book an event on selected date and time slots.

- So first, create a permission seeder, in which diifferent type of users, owner, admin and team-member will have different permissions. You decide whats best for them.
- After that, update the admin panel to reflect these permissions. routes + controllers + views.

- Secondly, Check my migrations, and 
