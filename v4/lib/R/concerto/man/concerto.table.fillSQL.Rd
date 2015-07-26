\name{concerto.table.fillSQL}
\alias{concerto.table.fillSQL}
\title{
Table helper function.
}
\description{
Fills SQL code with given parameters.
}
\usage{
concerto.table.fillSQL(sql, params = list())
}
\arguments{
  \item{sql}{
SQL code.
}
  \item{params}{
Named list with parameters.
}
}
\details{
}
\value{
SQL code with inserted parameters.
}
\references{
}
\author{
Przemyslaw Lis
}
\note{
}

\seealso{
}
\examples{
id <- 123
sql <- "SELECT * FROM `db`.`table` WHERE `id`={{id_value}}"
sql <- concerto.table.fillSQL(sql,list(id_value=id))
}

\keyword{ table }
\keyword{ MySQL }
\keyword{ database }
