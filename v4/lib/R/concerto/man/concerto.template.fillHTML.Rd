\name{concerto.template.fillHTML}
\alias{concerto.template.fillHTML}
\title{
Template helper function.
}
\description{
Fills template HTML with given parameters.
}
\usage{
concerto.template.fillHTML(html, params = list())
}
\arguments{
  \item{html}{
HTML template.
}
  \item{params}{
Named list with parameters.
}
}
\details{
}
\value{
HTML with inserted parameters.
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
html <- "<html><body>Inserted variable: {{param1}}</body></html>"
html <- concerto.template.fillHTML(html,list(param1="value"))
}

\keyword{ template }
\keyword{ HTML }
