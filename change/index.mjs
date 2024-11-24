import express from "express"
import session from "express-session"
import passport from "passport"
import env from "dotenv"
import cookieParser from "cookie-parser"
import connectPgSimple from "connect-pg-simple"

import login from "./src/routes/login.mjs"
import register from "./src/routes/register.mjs"
import main from "./src/routes/main.mjs"
import db from "./src/utils/database.mjs"

const app = express()
const port = 3000

env.config()

app.use(express.static("public"))



const PgSession = connectPgSimple(session);

app.use(cookieParser("helloworld"))
app.use(session({
    store: new PgSession({
        pool: db,
        tableName: 'session'
      }),
    secret: process.env.secretSession,
    resave: false,
    saveUninitialized: false
}))

app.use(passport.initialize())
app.use(passport.session())

//routes
app.use(login)
app.use(register)
app.use(main)

app.listen(port, ()=>{
    console.log(`Port ${port} is now running...`)
}) 